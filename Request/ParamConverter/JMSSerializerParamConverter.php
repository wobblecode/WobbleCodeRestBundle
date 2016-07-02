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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

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
     * @param Serializer $serializer JMS Serializer
     */
    public function __construct(Serializer $serializer, $validator = null)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {
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
        $object = $this->serializer->deserialize($request->getContent(), $this->class, 'json');
        $object->__construct();

        // {
        //     "errors": {
        //         "fields": {
        //             "to": [
        //                 "This value should not be blank."
        //             ]
        //         }
        //     }
        // }
        if (isset($options['validation']) && $options['validation']) {
            $errors = $this->validator->validate($object);

            if (count($errors) > 0) {
                $errorsMap = [];
                foreach ($errors as $error) {
                    $errorsMap['fields'][$error->getPropertyPath()][] = $error->getMessage();
                }
            }
        }

        $request->attributes->set('_payload_validation_errors', $errorsMap);
        $request->attributes->set($configuration->getName(), $object);
    }
}
