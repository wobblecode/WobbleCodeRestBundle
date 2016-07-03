<?php

namespace WobbleCode\RestBundle\Mapper;

class ErrorMapper
{
    /**
     * {@inheritdoc}
     */
    public function mapValidator($errors)
    {
        $errorsMap = [];
        foreach ($errors as $error) {
            $errorsMap['fields'][$error->getPropertyPath()][] = $error->getMessage();
        }

        return $errorsMap;
    }
}
