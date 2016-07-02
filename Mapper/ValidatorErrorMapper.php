<?php

namespace WobbleCode\RestBundle\Mapper;

class ValidatorErrorMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($errors)
    {
        $errorsMap = [];
        foreach ($errors as $error) {
            $errorsMap['fields'][$error->getPropertyPath()][] = $error->getMessage();
        }

        return $errorsMap;
    }
}
