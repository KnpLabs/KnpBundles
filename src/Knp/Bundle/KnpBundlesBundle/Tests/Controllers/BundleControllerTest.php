<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BundleControllerTest extends WebTestCase
{
    public function testListAll()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/\d+ bundles/i', str_replace("\n", '', trim($crawler->filter('h1')->text())));
    }

    public function testShow()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->filter('li.bundle a.name')->first()->link());
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/[\w\d]+Bundle/', str_replace("\n", '', trim($crawler->filter('h1')->text())));
        $this->assertEquals(1, $crawler->filter('div.markdown')->count());
    }

    public function testSearch()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/search');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $crawler = $client->submit($crawler->filter('form#search-box button')->form(), array('q' => 'ImQuiteSureThisWillReturnNothing'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('.repos-list li.bundle')->count());
        $crawler = $client->submit($crawler->filter('form#search-box button')->form(), array('q' => 'FooBundle'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(15, $crawler->filter('.bundles-list li.bundle')->count());
    }

    public function testLatest()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/latest?format=atom');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^<\?xml/', $client->getResponse()->getContent());
    }
}
