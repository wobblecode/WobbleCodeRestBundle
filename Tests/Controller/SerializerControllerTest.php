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

class SerializeControllerTest extends WebTestCase
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

        $bulkTask = [];
        for ($i=0; $i<=10; $i++) {
            $bulkTask[] = $task;
        }

        $taskInvalid = [
            'title' => '',
            'priority' => 100
        ];

        $bulkTaskInvalid = [];
        for ($i=0; $i<=10; $i++) {
            $bulkTaskInvalid[] = $taskInvalid;
        }

        return [
            ['POST', 'serialize/1', 'application/json', '"title":"Untitled"', null],
            ['POST', 'serialize-bulk/1', 'application/json', '"title":"Untitled"', null],
            ['POST', 'serialize/1', 'text/html', 'Task Untitled', null],
            ['POST', 'deserialize/1', 'application/json', 'Bad Request', null, 400],
            ['POST', 'deserialize-bulk/1', 'application/json', 'Bad Request', null, 400],
            ['POST', 'deserialize/1', 'application/json', 'Bad Request', '{"bad json",}', 400],
            ['POST', 'deserialize-bulk/1', 'application/json', 'Bad Request', '{"bad json",}', 400],
            ['POST', 'deserialize/1', 'application/json', '"title":"My Task"', json_encode($task)],
            ['POST', 'deserialize-bulk/1', 'application/json', '"title":"My Task"', json_encode($bulkTask)],
            ['POST', 'deserialize-bulk-limited/1', 'application/json', '"Payload Too Large"', json_encode($bulkTask), 413],
            ['POST', 'deserialize/1', 'application/json', '"completed":true', json_encode($task)],
            ['POST', 'deserialize/1', 'text/html', 'Task My Task', json_encode($task)],
            ['POST', 'deserialize-validation/default', 'application/json', 'completed":true', json_encode($task)],
            [
                'POST',
                'deserialize-validation/default',
                'application/json',
                'This value should not be blank',
                json_encode($taskInvalid), 422
            ],
            [
                'POST',
                'deserialize-validation/trial',
                'application/json',
                'Priority max 5',
                json_encode($taskInvalid), 422
            ],
            [
                'POST',
                'deserialize-validation-bulk/default',
                'application/json',
                'This value should not be blank',
                json_encode($bulkTaskInvalid), 422
            ],
            [
                'POST',
                'deserialize-validation-bulk/trial',
                'application/json',
                'Priority max 5',
                json_encode($bulkTaskInvalid), 422
            ],
            [
                'POST',
                'deserialize-validation-bulk-error-name/1',
                'application/json',
                '"priority":100',
                json_encode($bulkTaskInvalid), 422
            ],
        ];
    }
}
