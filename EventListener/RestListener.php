<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace WobbleCode\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;
use JMS\Serializer\Serializer;

class RestListener
{
    /**
     * Serializer
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constrictpr
     *
     * @param Serializer $serializer JSM Serializer for responses
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @todo detect api version
     */
    public function detectVersion($contentType)
    {
        return false;
    }

    /**
     * @todo detect content
     */
    public function detectFormat($contentType)
    {
        return 'json';
    }

    /**
     * Add No cache headers
     *
     * @param Response $response
     */
    public function addNoCacheHeaders(Response $response)
    {
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
    }

    /**
     *
     * @param Request $request
     * @param Array   $trigger
     *
     * @return Mixed   false or content accepted
     */
    public function checkAcceptedContent(Request $request, $acceptedContent)
    {
        if (in_array('all', $acceptedContent)) {
            return true;
        }

        foreach ($acceptedContent as $k => $v) {

            $triggered = preg_match('/'.preg_quote($v, '/').'/i', $request->headers->get('Accept'));

            if ($triggered) {
                return $v;
            }
        }

        return false;
    }

    public function checkForm($form)
    {
        $errors = array();

        foreach ($form as $key => $v) {
            if ($v->vars['errors']) {
                foreach ($v->vars['errors'] as $error) {
                    $errors[$v->vars['name']]['errors'][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request    = $event->getRequest();
        $parameters = $event->getControllerResult();
        $restConfig = $request->attributes->get('_rest');

        /**
         * Check if we enable REST
         */
        if ($restConfig == false) {
            return;
        }

        $accepted = $restConfig->getAcceptedContent();
        $trigger = $this->checkAcceptedContent($request, $accepted);

        if ($trigger == false) {
            return;
        }

        /**
         * Process form, getErrors, and throw 400 if not valid
         */
        if ($restConfig->getProcessForms()) {

            $formErrors = array();
            $form = false;

            $defaultFormParam = $restConfig->getDefaultFormParam();

            if (isset($parameters[$defaultFormParam])) {
                $form = $parameters[$defaultFormParam];
            }

            if ($form) {
                $formErrors = $this->checkForm($form);
            }

            if ($formErrors) {

                $content = array(
                    'entity' => $formErrors
                );

                $data = $this->serializer->serialize($content, 'json');
                $request->setRequestFormat('json');

                $response = new Response();
                $response->setContent($data);
                $response->setStatusCode(400);
                $response->headers->set('Content-Type', 'application/json');
                $this->addNoCacheHeaders($response);
                $event->setResponse($response);
                return;
            }

            unset($parameters[$defaultFormParam]);
        }

        /**
         * Process OutPuts
         */
        $params = array_intersect_key(
            $parameters,
            array_flip($restConfig->getOutput())
        );

        $data = $this->serializer->serialize($params, 'json');

        /**
         * Set response
         */
        $response = new Response();
        $response->setContent($data);
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        $this->addNoCacheHeaders($response);
        $event->setResponse($response);
    }
}
