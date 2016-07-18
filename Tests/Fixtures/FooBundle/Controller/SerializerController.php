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
     * @Route("/serialize-bulk/1")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createBulkTaskAction()
    {
        $task = [];
        for($i=0; $i<=1; $i++) {
            $task[] = new Task;
        }

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
     * @Rest()
     * @Route("/deserialize-bulk/1")
     * @Method("POST")
     * @ParamConverter("collection", class="Tests\Fixtures\FooBundle\Model\Task", converter="jms_serializer",
     *     options={
     *         "validation"=true,
     *         "collection"=true
     *     }
     * )
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamBulkTaskAction($collection)
    {
        return [
            'entity' => $collection
        ];
    }

    /**
     * @Rest()
     * @Route("/deserialize-validation-bulk-error-name/1")
     * @Method("POST")
     * @ParamConverter("collection", class="Tests\Fixtures\FooBundle\Model\Task", converter="jms_serializer",
     *     options={
     *         "validation"=true,
     *         "collection"=true,
     *         "collection_errors_name"="priority",
     *         "collection_errors_property"="getPriority",
     *     }
     * )
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamBulkErrorsNameTaskAction($collection)
    {
        return [
            'entity' => $collection
        ];
    }

    /**
     * @Rest()
     * @Route("/deserialize-bulk-limited/1")
     * @Method("POST")
     * @ParamConverter("collection", class="Tests\Fixtures\FooBundle\Model\Task", converter="jms_serializer",
     *     options={
     *         "validation"=true,
     *         "collection"=true,
     *         "collection_limit"=5
     *     }
     * )
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamBulkLimitedTaskAction($collection)
    {
        return [
            'entity' => $collection
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
     *     options={"validation"=true,"collection"=true,"collection_limit"=100}
     * )
     * @Rest()
     * @Route("/deserialize-validation-bulk/default")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamValidationBulkTaskAction(Task $task)
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

    /**
     * @ParamConverter(
     *     "task",
     *     class="Tests\Fixtures\FooBundle\Model\Task",
     *     converter="jms_serializer",
     *     options={
     *         "validation"=true,
     *    	   "validationGroups"={"trial"},
     *         "collection"=true,
     *         "collection_limit"=100
     *     }
     * )
     * @Rest()
     * @Route("/deserialize-validation-bulk/trial")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createParamValidationGroupBulkTaskAction(Task $task)
    {
        return [
            'entity' => $task
        ];
    }
}
