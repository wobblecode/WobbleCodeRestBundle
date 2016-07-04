<?php

namespace WobbleCode\RestBundle\Mapper;

use Symfony\Component\Form\FormView;

interface MapperInterface
{
    /**
     * Remaps the data schema into an array
     *
     * @param Array $data Array of data to remap
     *
     * @return Array Remapped data
     */
    public function mapValidator($data);

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
    public function mapForm(FormView $form);
}
