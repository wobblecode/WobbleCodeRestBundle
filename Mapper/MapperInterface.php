<?php

namespace WobbleCode\RestBundle\Mapper;

interface MapperInterface
{
    /**
     * Remaps the data schema into an array
     *
     * @param Array $data Array of data to remap
     *
     * @return Array Remapped data
     */
    public function map($data);
}
