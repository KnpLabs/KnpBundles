<?php

namespace Bundle\S2bBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testListAll()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'user_list'));
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertRegexp('/^All \d+ Symfony2 Developers$/', str_replace("\n", '', trim($crawler->filter('h1')->text())));

        $crawler = $client->request('GET', $this->generateUrl($client, 'user_list', array('sort' => 'name')));
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testShow()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $this->generateUrl($client, 'user_list'));
        $crawler = $client->click($crawler->filter('li.developer a.name')->first()->link());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    protected function generateUrl($client, $route, array $params = array())
    {
        return $client->getContainer()->get('router')->generate($route, $params);
    }
}
