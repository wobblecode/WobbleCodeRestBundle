<?php

namespace Tests\Fixtures\FooBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use WobbleCode\RestBundle\Configuration\Rest;
use Tests\Fixtures\FooBundle\Model\Task;
use Tests\Fixtures\FooBundle\Form\Type\TaskType;

/**
 * @Route()
 */
class FormController extends Controller
{
    /**
     * @Rest(payloadMapping="task")
     * @Route("/form/create/", name="form_create")
     * @Method("POST")
     * @Template("FooBundle:Serializer:task.html.twig")
     */
    public function createAction(Request $request)
    {
        $task = new Task;

        $form = $this->createForm('Tests\Fixtures\FooBundle\Form\Type\TaskType', $task, array(
            'method'             => 'POST',
            'action'             => $this->generateUrl('form_create'),
            'validation_groups'  => []
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return [
                'entity' => $task,
            ];
        }

        return [
            'entity' => $task,
            'form' => $form->createView()
        ];
    }
}
