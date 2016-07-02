<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://www.wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace WobbleCode\RestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormControllerTest extends WebTestCase
{
    /**
     * @dataProvider urlsProvider
     */
    public function testSerializationTriggering($method, $url, $accept, $expected, $data = null, $code = 200)
    {
        $client = static::createClient();
        $client->request(
            $method,
            $url,
            $data,
            [],
            [
                'HTTP_ACCEPT' => $accept,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        echo $client->getResponse()->getContent(); die();

        $this->assertEquals($code, $client->getResponse()->getStatusCode());
        $this->assertContains($expected, $client->getResponse()->getContent());
    }

    public function urlsProvider()
    {
        $task['task'] = [
            'title' => 'My Task',
            'completed' => true
        ];

        $taskInvalid['task'] = [
            'title' => '',
            'priority' => 100
        ];

        return [
            [
                'POST',
                '/form/create/',
                'text/html',
                'This value should not be blank',
                $taskInvalid,
                422
            ]
        ];
    }
}
