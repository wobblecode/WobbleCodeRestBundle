<?php

namespace Tests\Fixtures\FooBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

    /**
     * @Rest()
     * @Route("/deserialize/1")
     * @Method("POST")
     * @ParamConverter("task", class="Tests\Fixtures\FooBundle\Model\Task", converter="jms_serializer")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamTaskAction(Task $task)
    {
        return [
            'entity' => $task
        ];
    }

    /**
     * @ParamConverter(
     *     "task",
     *     class="Tests\Fixtures\FooBundle\Model\Task",
     *     converter="jms_serializer",
     *     options={"validation"=true}
     * )
     * @Rest()
     * @Route("/deserialize-validation/default")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamValidationTaskAction(Task $task)
    {
        return [
            'entity' => $task
        ];
    }

    /**
     * @ParamConverter(
     *     "task",
     *     class="Tests\Fixtures\FooBundle\Model\Task",
     *     converter="jms_serializer",
     *     options={
     *         "validation"=true,
     *    	   "validationGroups"={"trial"}
     *     }
     * )
     * @Rest()
     * @Route("/deserialize-validation/trial")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamValidationGroupTaskAction(Task $task)
    {
        return [
            'entity' => $task
        ];
    }
}
