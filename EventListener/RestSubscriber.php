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

use WobbleCode\RestBundle\Event\PreSerializeConfigurationEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Pagination\PaginationInterface;
use WobbleCode\RestBundle\Exception\ValidationException;
use WobbleCode\RestBundle\Mapper\MapperInterface;

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
     * @var bool
     */
    protected $isExecutable = true;

    /**
     * @var MapperInterface
     */
    protected $errorMapper;

    /**
     * Constructor
     *
     * @param Serializer $serializer JSM Serializer for responses
     */
    public function __construct(
        Serializer $serializer,
        MapperInterface $errorMapper
    ) {
        $this->dispatcher = new EventDispatcher();
        $this->serializationContext = new SerializationContext();
        $this->serializationContext->setSerializeNull(true);
        $this->serializationContext->enableMaxDepthChecks();

        $this->serializer = $serializer;
        $this->errorMapper = $errorMapper;
    }

    /**
     * Add metadata for limit, page and current page
     */
    public function decoupleMetadata($object, $paginationKey = 'entities')
    {
        if (isset($object[$paginationKey]) && $object[$paginationKey] instanceof PaginationInterface) {
            $entities = $object[$paginationKey];

            return [
                'metadata' => [
                    'count'          => (int) $entities->count(),
                    'total_count'    => (int) $entities->getTotalItemCount(),
                    'items_per_page' => (int) $entities->getItemNumberPerPage(),
                    'page_number'    => (int) $entities->getCurrentPageNumber()
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

        if (isset($restConfig) && (!$request->headers->get('Accept') || $request->headers->get('Accept') == "*/*")) {
            $request->headers->set('Accept', $restConfig->getDefaultAccept());
        }

        if (!$this->isExecutable($request, null)) {
            return;
        };

        if ($restConfig->getPayloadMapping()) {
            $content = $request->getContent();
            if (empty($content) === false) {
                $payload = @json_decode($content, true);

                if ($payload === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new BadRequestHttpException('Invalid JSON');
                }

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
     * Checks if REST features should be executed
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (!$this->isExecutable($request, $response)) {
            return;
        };
    }

    /**
     * Generates the content for the view
     *
     * @param GetResponseForControllerResultEvent $event [description]
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (!$this->isExecutable($request, $response)) {
            return;
        };

        $preRestConfig = $request->attributes->get('_rest');
        $restEvent = new PreSerializeConfigurationEvent($preRestConfig);
        $this->dispatcher->dispatch(PreSerializeConfigurationEvent::NAME, $restEvent);
        $restConfig = $restEvent->getConfiguration();
        $parameters = $event->getControllerResult();

        /**
         * Process form
         */
        if ($restConfig->getProcessForms()) {
            $formErrors = [];
            $form = false;

            $defaultFormParam = $restConfig->getDefaultFormParam();

            if (isset($parameters[$defaultFormParam])) {
                $form = $parameters[$defaultFormParam];
            }

            if ($form) {
                $formErrors = $this->errorMapper->mapForm($form);
            }

            if ($formErrors) {
                throw new ValidationException($formErrors);
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

        /**
         * Process serializer groups
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
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($data);
        $this->setStatusCode($request, $response, $parameters);
        $this->addNoCacheHeaders($response);
        $event->setResponse($response);
    }

    public function onValidationError(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof ValidationException === false) {
            return;
        }

        $errors = $exception->getErrors();

        $response = new Response();
        $response->setContent(json_encode(['errors' => $errors]));
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }

    /**
     * Checks if the REST features should be enabled. This method it's executed
     * on every step of the process:
     *
     *   postAnnotation -> onKernelResponse -> onKernelView
     *
     * @param Request       $request
     * @param null|Response $response Optional response
     */
    public function isExecutable(Request $request, $response = null)
    {
        if (!$this->isExecutable) {
            return false;
        }

        $restConfig = $request->attributes->get('_rest');

        if ($restConfig === null) {
            $this->isExecutable = false;

            return false;
        }

        // TODO default or annotation
        $accepted = $restConfig->getAcceptedContent();
        $trigger = $this->checkAcceptedContent($request, $accepted);

        if ($trigger === false) {
            $this->isExecutable = false;

            return false;
        }

        if ($restConfig->getInterceptRedirects() === false) {
            $this->isExecutable = false;

            return false;
        }

        if ($response === null) {
            return true;
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode == 400) {
            $this->isExecutable = false;

            return false;
        }

        if (preg_match('/^[45]/', $statusCode)) {
            $this->executable = false;

            return false;
        }

        if (preg_match('/^[3]/', $statusCode)) {
            $response->setContent(json_encode([]));
        }

        if ($response->isRedirect() === false) {
            $this->isExecutable = false;

            return false;
        }

        return true;
    }

    /**
     * Set status code to response based on request method or status code param
     *
     * @param Request  $request
     * @param Response $response
     */
    public function setStatusCode(Request $request, Response $response, $params = [])
    {
        if ($request->getMethod() == 'POST') {
            $response->setStatusCode(201);
        }

        if ($request->getMethod() == 'PUT') {
            $response->setStatusCode(200);
        }

        if ($request->getMethod() == 'DELETE') {
            $response->setContent(json_encode([]));
            $response->setStatusCode(204);
        }

        $restConfig = $request->attributes->get('_rest');
        $statusCodeParam = $restConfig->getStatusCodeParam();

        if ($statusCodeParam && isset($params[$statusCodeParam])) {
            $response->setStatusCode($params[$statusCodeParam]);
        }
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
     * Set serialize default option
     *
     * @param bool $serializeNull
     */
    public function setSerializeNull(bool $serializeNull)
    {
        $this->serializationContext->setSerializeNull($serializeNull);
    }

    /**
     * {@inheritdocs}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onValidationError', 10],
            KernelEvents::REQUEST => ['onKernelRequest', 0],
            KernelEvents::CONTROLLER => ['postAnnotations', -1],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
            KernelEvents::VIEW => ['onKernelView', 100],
        ];
    }
}
