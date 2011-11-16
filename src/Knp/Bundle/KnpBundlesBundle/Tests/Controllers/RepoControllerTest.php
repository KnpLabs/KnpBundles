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
    
    public function testAddInvalidRepo()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/add');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('button:contains("Add Repo")')->count());
        
        $form = $crawler->selectButton('add-repo-btn')->form();

        $form['repo'] = 'foobundle';
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Please enter a valid repo name")')->count() > 0);
    }

    public function testAddRepoUserNotFound()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/add');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('button:contains("Add Repo")')->count());
    
        $form = $crawler->selectButton('add-repo-btn')->form();
    
        $form['repo'] = 'foobaruserfoobar/foobundle';
        $crawler = $client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("Specified user was not found on GitHub")')->count() > 0);
    }
    
    public function testAddValidRepo()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/add');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('button:contains("Add Repo")')->count());

        $form = $crawler->selectButton('add-repo-btn')->form();

        $form['repo'] = 'doctrine/doctrine2';
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("We\'re fetching informations about this repo.")')->count() > 0);
    }
}
