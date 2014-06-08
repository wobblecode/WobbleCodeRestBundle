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
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RestKernelSuscriber implements EventSubscriberInterface
{
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

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $restConfig = $request->attributes->get('_rest');
        $statusCode = $response->getStatusCode();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($restConfig == false) {
            return;
        }

        $accepted = $restConfig->getAcceptedContent();
        $trigger = $this->checkAcceptedContent($request, $accepted);

        if ($trigger == false) {
            return;
        }

        if ($restConfig->getInterceptRedirects() == false) {
            return;
        }

        /**
         * Don't intercept 400 for bad requests
         */
        if ($statusCode == 400) {
            return;
        }

        /**
         * Intercept 3xx, 4xx, 5xx Exceptions and empty content
         */
        if (preg_match('/^[45]/', $statusCode)) {
            $response->setContent(null);
            return;
        }

        if (preg_match('/^[3]/', $statusCode)) {
            $response->setContent(null);
            return;
        }

        if ($response->isRedirect() == false) {
            return;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if ($request->getMethod() == 'POST') {

            $response->setStatusCode(201);
            $response->setContent(null);
        }

        if ($request->getMethod() == 'PUT') {

            $response->setStatusCode(200);
            $response->setContent(null);
        }

        if ($request->getMethod() == 'DELETE') {

            $response->setStatusCode(200);
            $response->setContent(null);
        }

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', 0),
        );
    }
}
