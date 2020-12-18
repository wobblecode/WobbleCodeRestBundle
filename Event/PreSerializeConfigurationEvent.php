<?php

namespace WobbleCode\RestBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use WobbleCode\RestBundle\Configuration\Rest;

class PreSerializeConfigurationEvent extends Event
{
    public const NAME = 'wobblecode_rest.pre_serialize_configuration';

    protected $configuration;

    public function __construct(Rest $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): Rest
    {
        return $this->configuration;
    }
}
