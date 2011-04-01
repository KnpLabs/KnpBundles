<?php

namespace Bundle\S2bBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BundleControllerTest extends WebTestCase
{
    public function testListAll()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/bundle');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/^All \d+ Bundles$/', str_replace("\n", '', trim($crawler->filter('h1')->text())));

        $crawler = $client->request('GET', '/bundle/name');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testShow()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/bundle');
        $crawler = $client->click($crawler->filter('li.repo a.name')->first()->link());
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/^[\w\d]+Bundle$/', str_replace("\n", '', trim($crawler->filter('h1')->text())));
        $this->assertEquals(1, $crawler->filter('div.markdown')->count());
    }

    public function testSearch()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/search');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $crawler = $client->submit($crawler->filter('form#search-box button')->form(), array('q' => 'ImQuiteSureThisWillReturnNothing'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('.repos-list li.repo')->count());
        $crawler = $client->submit($crawler->filter('form#search-box button')->form(), array('q' => '15'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(2, $crawler->filter('.repos-list li.repo')->count());
    }

    public function testLatest()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/latest.atom');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^<\?xml/', $client->getResponse()->getContent());
    }

}

