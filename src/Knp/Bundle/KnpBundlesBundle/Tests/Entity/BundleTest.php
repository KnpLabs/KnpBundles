<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Score;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDaysSinceLastCommit()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setLastCommitAt(new \Datetime('-31 days'));
        $this->assertEquals(31, $bundle->getDaysSinceLastCommit());
    }

    public function testIsInitializedTrue()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $this->assertFalse($bundle->isInitialized());
    }

    public function testIsInitializedFalse()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setNbForks(1);
        $this->assertTrue($bundle->isInitialized());
    }

    /**
     * @test
     */
    public function shouldHaveChangesWithSameScore()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setScore(1000);
        $bundle->setReadme('readme number one');
        $bundle->setLastCommitAt(new \DateTime('-10 day'));
        $bundle->setNbFollowers(100);
        $bundle->setNbForks(10);

        $beforeChange = $bundle->getStatusHash();

        $bundle->setReadme('readme number two');
        $bundle->setLastCommitAt(new \DateTime());
        $bundle->setNbFollowers(10);
        $bundle->setNbForks(100);

        $afterChange = $bundle->getStatusHash();

        $this->assertNotEquals($beforeChange, $afterChange);
    }

    /**
     * @test
     */
    public function shouldNotHaveChangesWithOnlyChangedScore()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setScore(1000);
        $bundle->setReadme('readme number one');
        $bundle->setLastCommitAt(new \DateTime('-10 day'));
        $bundle->setNbFollowers(100);
        $bundle->setNbForks(10);

        $beforeChange = $bundle->getStatusHash();

        $bundle->setScore(1100);

        $afterChange = $bundle->getStatusHash();

        $this->assertEquals($beforeChange, $afterChange);
    }
}
