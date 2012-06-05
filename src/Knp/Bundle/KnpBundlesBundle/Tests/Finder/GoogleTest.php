<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Symfony\Component\DomCrawler\Crawler;

class GoogleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBuildValidUrl()
    {
        $callCounter = 0;

        $crawler = $this->getMock('Symfony\Component\DomCrawler\Crawler');
        $crawler->expects($this->any())
            ->method('filter')
            ->will($this->returnSelf());
        $crawler->expects($this->any())
            ->method('extract')
            ->will($this->returnCallback(function () use (&$callCounter) {
                if ($callCounter) {
                    return array('test' => 'https://github.com/KnpLabs/KnpBundles');
                }
                $callCounter++;

                return array('test2' => 'https://github.com/l3l0/KnpBundles');
            }));

        $client = $this->getMock('Goutte\Client', array('request'));
        $client->expects($this->at(0))
            ->method('request')
            ->with('GET', 'http://www.google.com/search?q=Symfony2')
            ->will($this->returnValue($crawler));
        $client->expects($this->at(1))
            ->method('request')
            ->with('GET', 'http://www.google.com/search?q=Symfony2&start=10')
            ->will($this->returnValue($crawler));

        $finder = new Google('Symfony2', 2, $client);
        $repos = $finder->find();

        $this->assertEquals(array('l3l0/KnpBundles', 'KnpLabs/KnpBundles'), $repos);
    }

    /**
     * @dataProvider getExtractPageUrlsData
     * @test
     */
    public function shouldExtractPageUrlsFromGoogleHtml($node, $expected)
    {
        $callCounter = 0;

        $client = $this->getMock('Goutte\Client', array('request'));
        $client->expects($this->any())
            ->method('request')
            ->will($this->returnCallback(function () use ($node, &$callCounter) {
                $callCounter++;
                if (1 == $callCounter) {
                    return new Crawler($node);
                }

                return new Crawler();
            }));

        $finder = new Google('Symfony2', 3, $client);
        $values = $finder->find();

        $this->assertEquals($expected, $values);
    }

    public function getExtractPageUrlsData()
    {
        return array(
            array(
                '<html><head></head><body></body></html>',
                array()
            ),
            array(
                file_get_contents(__DIR__.'/Fixtures/google-results.html'),
                array(
                    'foo/bar',
                    'foo/bar2'
                )
            )
        );
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldNotUseEmptyQuery()
    {
        $finder = new Google('', 3);
        $finder->find();
    }
}
