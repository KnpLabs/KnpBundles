<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Finder;

use Knp\Bundle\KnpBundlesBundle\Finder\Github;

class GithubTest extends FinderTestCase
{
    /**
     * @dataProvider getExtractPageUrlsData
     * @test
     */
    public function shouldExtractPageUrlsFromGithubHtml($node, $expected)
    {
        $browser = $this->getBrowserMock($node, 'https://github.com/search?q=Symfony2&type=Repositories&language=PHP&start_value=3');

        $finder = new Github('Symfony2', 3);
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
                file_get_contents(__DIR__.'/Fixtures/github-results.html'),
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
        new Github('', 3);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldNotUseLimitNearZero()
    {
        new Github('test', 0);
    }
}
