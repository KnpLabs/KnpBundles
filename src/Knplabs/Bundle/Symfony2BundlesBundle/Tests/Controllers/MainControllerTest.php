<?php

namespace Bundle\S2bBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("Symfony2 Bundles")')->count());
        $this->assertEquals(1, $crawler->filter('input#search-query')->count());
    }

    public function testApi()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/api');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("Developer API")')->count());
        $this->assertEquals(1, $crawler->filter('h3:contains("List all Bundles")')->count());
    }

    public function testMenu()
    {
        $menu = $this->createClient()->request('GET', '/')->filter('#menu');
        $this->assertEquals(1, $menu->count());

        $this->assertEquals('home current first', $menu->filter('li')->first()->attr('class'));
        $this->assertEquals('Home', $menu->filter('li')->first()->filter('a')->text());
        $this->assertEquals('last', $menu->filter('li')->last()->attr('class'));
    }
}
