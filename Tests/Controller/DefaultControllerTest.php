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
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // $crawler = $client->request('GET', '/');
        // $this->assertContains('hello', $crawler->filter('#container h1')->text());
    }
}
