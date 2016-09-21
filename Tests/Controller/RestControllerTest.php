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

class RestControllerTest extends WebTestCase
{
    /**
     * @dataProvider urlsProvider
     */
    public function testRestTriggering($url, $accept, $expected, $code = 200)
    {
        $client = static::createClient();
        $client->request(
            'GET',
            $url,
            [],
            [],
            [
                'HTTP_ACCEPT' => $accept,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertEquals($code, $client->getResponse()->getStatusCode());
        $this->assertContains($expected, $client->getResponse()->getContent());
    }

    public function urlsProvider()
    {
        return [
            ['basic/1', 'text/html', 'Hello Luis'],
            ['basic/1', 'application/json', '{"entity":{"name":"Luis"}}'],
            ['basic/1', null, 'Hello Luis'],
            ['default/1', 'text/html', 'Hello Luis'],
            ['default/1', null, '{"entity":{"name":"Luis"}}'],
            ['all/1', 'text/html', '{"entity":{"name":"Luis"}}'],
            ['all/1', 'application/json', '{"entity":{"name":"Luis"}}'],
            ['all/1', null, '{"entity":{"name":"Luis"}}'],
            ['httpcode/1', 'application/json', 'Luis', 208],
            ['customcode/1', 'application/json', 'Luis', 208],
        ];
    }
}
