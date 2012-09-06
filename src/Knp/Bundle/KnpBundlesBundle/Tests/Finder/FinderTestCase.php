<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Finder;

abstract class FinderTestCase
{
    protected function getBrowserMock($node, $url)
    {
        $revert = libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML($node);

        libxml_use_internal_errors($revert);

        $response = $this->getMock('Buzz\Message\Response');
        $response->expects($this->any())
            ->method('toDomDocument')
            ->will($this->returnValue($dom));

        $browser = $this->getMock('Buzz\Browser');
        $browser->expects($this->any())
            ->method('get')
            ->with($url)
            ->will($this->returnValue($response));

        return $browser;
    }
}
