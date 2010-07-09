<?php
namespace Bundle\S2bBundle\Tests\Controller;

use Symfony\Framework\FoundationBundle\Test\WebTestCase;

class BundleControllerTest extends WebTestCase
{
    public function testListAll()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/bundle');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/^\d+ Bundles$/', str_replace("\n", '', trim($crawler->filter('h1')->text())));
        $this->assertEquals(1, $crawler->filter('p.slogan:contains("All Bundles sorted by score")')->count());
        $this->assertTrue(10 < $crawler->filter('.bundle-list li.item')->count());

        $crawler = $client->request('GET', '/bundle/name');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('p.slogan:contains("All Bundles sorted by name")')->count());
    }

    public function testShow()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/bundle');
        $crawler = $client->click($crawler->filter('li.item a.item-link')->first()->link());
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/^[\w\d]+Bundle$/', str_replace("\n", '', trim($crawler->filter('h1')->text())));
        $this->assertEquals(1, $crawler->filter('div.markdown')->count());
    }

    public function testSearch()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/search');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $crawler = $client->submit($crawler->filter('form#quick-search button')->form(), array('q' => 'ImQuiteSureThisWillReturnNothing'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('.bundle-list li.item')->count());
        $crawler = $client->submit($crawler->filter('form#quick-search button')->form(), array('q' => 'image'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('.bundle-list li.item a:contains("ImagineBundle")')->count());
    }

    public function testLatest()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/latest.atom');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertRegexp('/^<\?xml/', $client->getResponse()->getContent());
    }

}

