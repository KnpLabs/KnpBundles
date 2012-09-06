<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Finder;

use Knp\Bundle\KnpBundlesBundle\Finder\Aggregate;

class AggregateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function findShouldMergeResultsFromAggregatedFinders()
    {
        $mockFirstFinder = $this->getFinderInterfaceMock(array('test/TestBundle'));
        $mockSecondFinder = $this->getFinderInterfaceMock(array('test2/TestBundle'));

        $expected = array(
            'test/TestBundle', 'test2/TestBundle'
        );
        sort($expected);

        $finder = new Aggregate(array($mockFirstFinder, $mockSecondFinder));
        $result = $finder->find();
        sort($result);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findShouldReturnsUniqueResults()
    {
        $mockFirstFinder = $this->getFinderInterfaceMock(array('KnpLabs/KnpBundles', 'test/TestBundle'));
        $mockSecondFinder = $this->getFinderInterfaceMock(array('KnpLabs/KnpBundles', 'test2/TestBundle'));

        $finder = new Aggregate(array($mockFirstFinder, $mockSecondFinder));

        $this->assertCount(3, $finder->find());
    }

    private function getFinderInterfaceMock($returned)
    {
        $mock = $this->getMock('Knp\Bundle\KnpBundlesBundle\Finder\FinderInterface');
        $mock->expects($this->any())
            ->method('find')
            ->will($this->returnValue($returned));

        return $mock;
    }
}
