<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Knp\Bundle\KnpBundlesBundle\Command\KbGenerateBadgesCommand;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

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

    public function testSearchByKeyword()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/keyword/foo');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $crawler = $client->request('GET', '/keyword/CrazyKeywordNeverSeen');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(0, $crawler->filter('.repos-list li.bundle')->count());
    }

    public function testIAmUsingThisRepo()
    {
        $this->markTestIncomplete("You should log in here");

        $client = self::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $link = $crawler->filter('a:contains("I\'m using")')->first()->link();
        $crawler = $client->click($link);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('li.bundle-users img')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("I am using this bundle")')->count());
    }

    public function testScoreDetails()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/Dexter/DexterFooBundle');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(1, $crawler->filter('h3#bundle-score-details')->count());
    }

    public function testBadge()
    {
        $client = self::createClient();

        $application = new Application($client->getKernel());
        $application->setAutoExit(false);
        $application->run(new StringInput('kb:generate:badges'), new NullOutput());

        $crawler = $client->request('GET', '/Dexter/DexterFooBundle/badge');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals($client->getResponse()->headers->get('Content-Type'), 'image/png');
    }
}
