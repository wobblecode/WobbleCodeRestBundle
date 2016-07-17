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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\Serializer;
use JMS\Serializer\Exception\Exception;
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

        if ($configuration->getClass() == 'Symfony\Component\HttpKernel\Log\DebugLoggerInterface') {
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
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        if (!$request->getContent()) {
            throw new BadRequestHttpException('Invalid JSON. The payload is empty');
        }

        try {
            $object = $this->serializer->deserialize($request->getContent(), $this->class, 'json');
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $object->__construct();

        $validationGroups = ['Default'];

        if (isset($options['validationGroups'])) {
            $validationGroups = $options['validationGroups'];
        }

        if (isset($options['validation']) && $options['validation']) {
            $errors = $this->validator->validate($object, null, $validationGroups);

            if (count($errors) > 0) {
                $mappedErrors = $this->errorMapper->mapValidator($errors);
                $request->attributes->set('_payload_validation_errors', $mappedErrors);
            }
        }

        $request->attributes->set($configuration->getName(), $object);
    }
}
