<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("bundles")')->count());
        $this->assertEquals(1, $crawler->filter('input#search-query')->count());
    }

    public function testApi()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/api');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("API")')->count());
        $this->assertEquals(1, $crawler->filter('h3:contains("List all Bundles")')->count());
    }

    public function testMenu()
    {
        $menu = self::createClient()->request('GET', '/')->filter('#menu');
        $this->assertEquals(1, $menu->count());

        $this->assertEquals('current first', $menu->filter('li')->first()->attr('class'));
        $this->assertEquals('Bundles', $menu->filter('li')->first()->filter('a')->text());
        $this->assertEquals('last', $menu->filter('li')->last()->attr('class'));
    }
}
