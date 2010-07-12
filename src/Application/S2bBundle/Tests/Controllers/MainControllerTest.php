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
        $this->assertEquals(1, $crawler->filter('input#qsearch')->count());
    }

    public function testApi()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/api');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h1:contains("Developer API")')->count());
        $this->assertEquals(1, $crawler->filter('h3:contains("List all Bundles")')->count());
    }
}
