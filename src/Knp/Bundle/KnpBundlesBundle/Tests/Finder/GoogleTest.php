<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Finder;

use Symfony\Component\DomCrawler\Crawler;
use Knp\Bundle\KnpBundlesBundle\Finder\Google;

class GoogleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getExtractPageUrlsData
     * @test
     */
    public function shouldExtractPageUrlsFromGoogleHtml($node, $expected)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->add($node);

        $response = $this->getMock('Buzz\Message\Response');
        $response->expects($this->any())
            ->method('toDomDocument')
            ->will($this->returnValue($dom));

        $browser = $this->getMock('Buzz\Browser');
        $browser->expects($this->any())
            ->method('get')
            ->with('http://www.google.com/search?q=Symfony2&start=20')
            ->will($this->returnValue($response));

        $finder = new Google('Symfony2', 3);
        $finder->setBrowser($browser);

        $this->assertEquals($expected, $finder->find());
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
        new Google('', 3);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldNotUseLimitNearZero()
    {
        new Google('test', 0);
    }
}
