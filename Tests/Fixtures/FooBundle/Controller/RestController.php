<?php

namespace Tests\Fixtures\FooBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="test.rest.multiple")
 */
class RestController
{
    /**
     * @Route("/1")
     * @Template("FooBundle:Rest:item.html.twig")
     */
    public function getItemAction()
    {
        return [
            'entity' => [
                'name' => 'Luis'
            ]
        ];
    }
}
