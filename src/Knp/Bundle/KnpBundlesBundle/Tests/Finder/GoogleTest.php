<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Finder;

use Knp\Bundle\KnpBundlesBundle\Finder\Google;

class GoogleTest extends FinderTestCase
{
    /**
     * @dataProvider getExtractPageUrlsData
     * @test
     */
    public function shouldExtractPageUrlsFromGoogleHtml($node, $expected)
    {
        $browser = $this->getBrowserMock($node, 'http://www.google.com/search?q=Symfony2&start=20');

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
