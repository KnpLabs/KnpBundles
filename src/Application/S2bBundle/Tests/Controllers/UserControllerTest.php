<?php

namespace Bundle\S2bBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testListAll()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'user_list'));
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/^\d+ Developers$/', str_replace("\n", '', trim($crawler->filter('h1')->text())));
        $this->assertEquals(1, $crawler->filter('p.slogan:contains("List of Symfony2 Bundle developers")')->count());
        $this->assertTrue(10 < $crawler->filter('.user-list li.item')->count());

        $crawler = $client->request('GET', $this->generateUrl($client, 'user_list', array('sort' => 'name')));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('p.slogan:contains("List of Symfony2 Bundle developers")')->count());
    }

    public function testShow()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'user_list'));
        $crawler = $client->click($crawler->filter('li.item a.item-link')->first()->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h3:contains("Last commits")')->count());
    }

    protected function generateUrl($client, $route, array $params = array())
    {
        return $client->getContainer()->get('router')->generate($route, $params);
    }

}
