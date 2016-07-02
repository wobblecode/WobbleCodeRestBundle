<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://www.wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace WobbleCode\RestBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use WobbleCode\RestBundle\Exception\ValidationException;

class RestSubscriber implements EventSubscriberInterface
{
    /**
     * Serializer
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * SerializationContext
     *
     * @var SerializationContext
     */
    protected $serializationContext;

    /**
     * Constructor
     *
     * @param Serializer $serializer JSM Serializer for responses
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializationContext = new SerializationContext();
        $this->serializationContext->setSerializeNull(true);
        $this->serializationContext->enableMaxDepthChecks();

        $this->serializer = $serializer;
    }

    /**
     * Add metadata for limit, page and current page
     */
    public function decoupleMetadata($object, $paginationKey = 'entities')
    {
        if (isset($object[$paginationKey]) && $object[$paginationKey] instanceof SlidingPagination) {
            $entities = $object[$paginationKey];

            return [
                'metadata' => [
                    'count'          => $entities->count(),
                    'total_count'    => $entities->getTotalItemCount(),
                    'items_per_page' => $entities->getItemNumberPerPage(),
                    'page_number'    => $entities->getCurrentPageNumber()
                ],
                $paginationKey => $entities->getItems()
            ];
        }

        return $object;
    }

    /**
     *
     * @param Request $request
     * @param array $acceptedContent
     *
     * @return Mixed false or string with content accepted or true if all is
     * accepted
     */
    public function checkAcceptedContent(Request $request, array $acceptedContent)
    {
        if (in_array('all', $acceptedContent)) {
            return true;
        }

        foreach ($acceptedContent as $v) {
            $triggered = preg_match('/'.preg_quote($v, '/').'/i', $request->headers->get('Accept'));
            if ($triggered) {
                return $v;
            }
        }

        return false;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->checkAcceptedContent($request, ['application/json'])) {
            $request->setRequestFormat('json');
            $request->attributes->add(['_format' => 'json']);
        }
    }

    public function postAnnotations(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $restConfig = $request->attributes->get('_rest');

        if ($restConfig === null) {
            return;
        }

        if (!$request->headers->get('Accept')) {
            $request->headers->set('Accept', $restConfig->getDefaultAccept());
        }

        $trigger = $this->checkAcceptedContent(
            $request,
            $restConfig->getAcceptedContent()
        );

        if ($trigger === false) {
            return;
        }

        if ($restConfig->getPayloadMapping()) {
            $content = $request->getContent();
            if (empty($content) === false) {
                $payload = json_decode($content, true);
                $request->request->add(array($restConfig->getPayloadMapping() => $payload));
            }
        }

        /**
         * Remove controller _template to avoid the lookup
         */
        $request->attributes->add(array('_template' => null));

        /**
         * Check for payload validations
         */
        $payloadErrors = $request->attributes->get('_payload_validation_errors');

        if ($payloadErrors) {
            throw new ValidationException($payloadErrors);
        }
    }

    /**
     * @todo decouple into HTTP Methods GET, POST, PUT, PATCH, DELETE
     * @param  FilterResponseEvent $event [description]
     *
     * @return [type]                     [description]
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $restConfig = $request->attributes->get('_rest');
        $statusCode = $response->getStatusCode();

        if ($restConfig === null) {
            return;
        }

        $accepted = $restConfig->getAcceptedContent();
        $trigger = $this->checkAcceptedContent($request, $accepted);

        if ($trigger === false) {
            return;
        }

        if ($restConfig->getInterceptRedirects() === false) {
            return;
        }

        /**
         * Don't intercept 400 for bad requests
         */
        if ($statusCode == 400) {
            return;
        }

        if (preg_match('/^[45]/', $statusCode)) {
            return;
        }

        if (preg_match('/^[3]/', $statusCode)) {
            $response->setContent(json_encode([]));
        }

        if ($response->isRedirect() === false) {
            return;
        }

        /**
         * If no exception is thrown, set status code to 201
         */
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if ($request->getMethod() == 'POST') {
            $response->setStatusCode(201);
        }

        if ($request->getMethod() == 'PUT') {
            $response->setStatusCode(200);
        }

        /**
         * @todo check if there is no content in the response for 204 if there
         * is some repsonse should be 200
         */
        if ($request->getMethod() == 'DELETE') {
            $response->setContent(json_encode([]));
            $response->setStatusCode(204);
        }

        $event->setResponse($response);
    }

    /**
     * Add No cache headers
     *
     * @todo add feature to control cache
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
     * This method process the form erros and remaps to a proper schema
     *
     * @todo should check if there is Unique contstraints to send 409 status
     * code if those contstraints fails.
     *
     * @param Symfony\Component\Form $form Form Object
     *
     * @return array
     */
    public function checkForm($form)
    {
        $errors = [];

        foreach ($form->vars['errors'] as $error) {
            $errors['main'][] = $error->getMessage();
        }

        foreach ($form as $v) {
            if (isset($v->vars['errors']) && $v->vars['errors']) {
                foreach ($v->vars['errors'] as $error) {
                    $errors['fields'][$v->vars['name']][] = $error->getMessage();
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

        if ($restConfig === null) {
            return;
        }

        $accepted = $restConfig->getAcceptedContent();
        $trigger = $this->checkAcceptedContent($request, $accepted);

        if ($trigger === false) {
            return;
        }

        /**
         * Process form
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
                    'errors' => $formErrors
                );

                $data = $this->serializer->serialize(
                    $content,
                    'json',
                    $this->serializationContext
                );

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

        /**
         * It decouples KnpPaginator metadata
         */
        $decoupleMetadata = $this->decoupleMetadata($params);
        $params = array_merge($params, $decoupleMetadata);

        if (isset($parameters['metadata'])) {
            $params = array_merge_recursive($params, ['metadata' => $parameters['metadata']]);
        }

        /**
         * Proces serializer groups
         */
        $serializeGroups = $restConfig->getSerializeGroups();

        if (count($serializeGroups)) {
            $this->serializationContext->setGroups($serializeGroups);
        }

        $data = $this->serializer->serialize(
            $params,
            'json',
            $this->serializationContext
        );

        /**
         * Set response
         */
        $response = new Response();
        $response->setContent($data);
        $this->addNoCacheHeaders($response);
        $event->setResponse($response);
    }

    public function onValidationError(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof ValidationException == false) {
            return;
        }

        $errors = $exception->getErrors();

        $response = new Response();
        $response->setContent(json_encode(['errors' => $errors]));
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => ['onValidationError', 0],
            KernelEvents::REQUEST => ['onKernelRequest', 0],
            KernelEvents::CONTROLLER => ['postAnnotations', -1],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
            KernelEvents::VIEW => ['onKernelView', 100],
        );
    }
}
