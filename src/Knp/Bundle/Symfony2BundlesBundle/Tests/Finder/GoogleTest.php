<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Finder;

use Symfony\Component\DomCrawler\Crawler;

class GoogleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getBuildUrlData
     */
    public function testBuildUrl($query, $arguments, $expected)
    {
        $finder = new Google($query);

        $method = new \ReflectionMethod($finder, 'buildUrl');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invokeArgs($finder, $arguments));
    }

    public function getBuildUrlData()
    {
        return array(
            array(
                'Symfony2',
                array(1),
                'http://www.google.com/search?q=Symfony2'
            ),
            array(
                'Symfony2',
                array(2),
               'http://www.google.com/search?q=Symfony2&start=10'
           )
        );
    }

    /**
     * @dataProvider getExtractPageUrlsData
     */
    public function testExtractPageUrls($node, $expected)
    {
        $crawler = new Crawler($node);

        $finder = new Google('Symfony2');

        $method = new \ReflectionMethod($finder, 'extractPageUrls');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($finder, $crawler));
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
                    'https://github.com/foo/bar',
                    'http://nothing.tld',
                    'https://github.com/foo/bar2'
                )
            )
        );
    }

    /**
     * @dataProvider getExtractUrlRepositoryData
     */
    public function testExtractUrlRepository($url, $expected)
    {
        $finder = new Google('Symfony2');

        $method = new \ReflectionMethod($finder, 'extractUrlRepository');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($finder, $url));
    }

    public function getExtractUrlRepositoryData()
    {
        return array(
            array(
                'https://github.com/foo/bar',
                'foo/bar'
            ),
            array(
                'https://github.com/dashed-username/dashed-repository',
                'dashed-username/dashed-repository'
            ),
            array(
                'https://github.com/underscored_username/underscored_repository',
                'underscored_username/underscored_repository'
            ),
            array(
                'https://github.com/foo',
                null
            ),
            array(
                'http://github.com/foo/bar',
                'foo/bar'
            ),
            array(
                'https://www.github.com/foo/bar',
                'foo/bar'
            ),
            array(
                'http://www.github.com/foo/bar',
                'foo/bar'
            )
        );
    }
}
