<?php

namespace Tests\Fixtures\FooBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use WobbleCode\RestBundle\Configuration\Rest;
use Tests\Fixtures\FooBundle\Model\Task;

/**
 * @Route()
 */
class SerializerController
{
    /**
     * @Rest()
     * @Route("/serialize/1")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createTaskAction()
    {
        $task = new Task;

        return [
            'entity' => $task
        ];
    }
}
