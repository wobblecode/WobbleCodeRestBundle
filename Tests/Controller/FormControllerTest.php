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
            [],
            [],
            [
                'HTTP_ACCEPT' => $accept,
                'CONTENT_TYPE' => 'application/json'
            ],
            $data
        );

        $this->assertEquals($code, $client->getResponse()->getStatusCode());
        $this->assertContains($expected, $client->getResponse()->getContent());
    }

    public function urlsProvider()
    {
        $task = [
            'title' => 'My Task',
            'priority' => 7
        ];

        $taskInvalid = [
            'title' => '',
            'priority' => 220
        ];

        return [
            [
                'POST',
                '/form/create/',
                'application/json',
                '"priority":7',
                json_encode($task),
                201
            ],
            [
                'POST',
                '/form/create/',
                'application/json',
                '"fields":{"title":["This value should not be blank."',
                json_encode($taskInvalid),
                422
            ]
        ];
    }
}
