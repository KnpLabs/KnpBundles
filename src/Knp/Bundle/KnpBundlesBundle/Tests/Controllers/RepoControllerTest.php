<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RepoControllerTest extends WebTestCase
{
    public function testLinks()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());
        
        $link = $crawler->filter('a:contains("FooBundle")')->first()->link();
        $crawler = $client->click($link);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('button:contains("Add URL")')->count());
    }
}
