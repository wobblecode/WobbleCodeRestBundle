<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://www.wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace WobbleCode\RestBundle\Request\ParamConverter;

use JMS\Serializer\DeserializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\Serializer;
use JMS\Serializer\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use WobbleCode\RestBundle\Mapper\MapperInterface;

class JMSSerializerParamConverter implements ParamConverterInterface
{
    /**
     * @var $class
     */
    private $class;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var MapperInterface
     */
    private $errorMapper;

    /**
     * @param Serializer $serializer JMS Serializer
     */
    public function __construct(
        Serializer $serializer,
        $validator = null,
        $errorMapper = null
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->errorMapper = $errorMapper;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {
        if ('jms_serializer' !== $configuration->getConverter()) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        if ($configuration->getClass() === 'Symfony\Component\HttpKernel\Log\DebugLoggerInterface') {
            return false;
        }

        $this->class = $configuration->getClass();

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        if (!$request->getContent()) {
            throw new BadRequestHttpException('Invalid JSON. The payload is empty');
        }

        $context = new DeserializationContext();
        if (isset($options['deserializationGroups']) && $options['deserializationGroups']) {
            $context->setGroups($options['deserializationGroups']);
        }

        try {
            if (isset($options['collection']) && $options['collection']) {
                $collection = $this->serializer->deserialize(
                    $request->getContent(),
                    'ArrayCollection<'.$this->class.'>',
                    'json',
                    $context
                );

                if (isset($options['collection_limit']) && $options['collection_limit']) {
                    if (count($collection) > $options['collection_limit']) {
                        throw new HttpException(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'Request entity too large');
                    }
                }

                foreach ($collection as $object) {
                    $object->__construct();
                }

                $request->attributes->set($configuration->getName(), $collection);
            } else {
                $object = $this->serializer->deserialize($request->getContent(), $this->class, 'json', $context);
                $object->__construct();

                $request->attributes->set($configuration->getName(), $object);
            }
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $validationGroups = ['Default'];

        if (isset($options['validationGroups'])) {
            $validationGroups = $options['validationGroups'];
        }

        if (isset($options['validation']) && $options['validation']) {
            if (isset($options['collection']) && $options['collection']) {
                $mappedErrors = [];
                foreach ($collection as $object) {
                    $error = $this->validateErrors($object, $validationGroups, $options, true);

                    if ($error) {
                        $mappedErrors[] = $error;
                    }
                }
            } else {
                $mappedErrors = $this->validateErrors($object, $validationGroups);
            }

            $request->attributes->set('_payload_validation_errors', $mappedErrors);
        }
    }

    private function validateErrors($object, $validationGroups, $options = null, $collection = null)
    {
        $errors = $this->validator->validate($object, null, $validationGroups);

        if (count($errors) > 0) {
            if ($collection) {
                $name = isset($options['collection_errors_name']) ? $options['collection_errors_name'] : null;
                $id = isset($options['collection_errors_property']) ? $object->{$options['collection_errors_property']}() : null;

                $mappedErrors = $this->errorMapper->mapValidator($errors, $name, $id);
            } else {
                $mappedErrors = $this->errorMapper->mapValidator($errors);
            }

            return $mappedErrors;
        }

        return null;
    }
}
