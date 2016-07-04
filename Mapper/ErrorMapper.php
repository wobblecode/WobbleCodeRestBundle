<?php

namespace WobbleCode\RestBundle\Mapper;

use Symfony\Component\Form;
use WobbleCode\RestBundle\Mapper\MapperInterface;

class ErrorMapper implements MapperInterface
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

    /**
     * {@inheritdoc}
     */
    public function mapForm(Form $form)
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
}
