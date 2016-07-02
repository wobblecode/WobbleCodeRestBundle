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

class DeserializeControllerTest extends WebTestCase
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
            'completed' => true
        ];

        return [
            ['POST', 'serialize/1', 'application/json', '"title":"Untitled"', null],
            ['POST', 'serialize/1', 'text/html', 'Task Untitled', null],
            ['POST', 'deserialize/1', 'application/json', '"title":"Untitled"', null],
            ['POST', 'deserialize/1', 'application/json', '"title":"My Task"', json_encode($task)],
            ['POST', 'deserialize/1', 'application/json', '"completed":true', json_encode($task)],
            ['POST', 'deserialize/1', 'text/html', 'Task My Task', json_encode($task)]
        ];
    }
}
