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
    public function testGetItem()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/default/1',
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        echo $client->getResponse()->getContent(); die();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Hello Luis', $client->getResponse()->getContent());
    }
}
