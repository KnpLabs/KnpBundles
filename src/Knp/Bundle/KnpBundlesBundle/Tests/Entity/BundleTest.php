<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDaysSinceLastCommit()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setLastCommitAt(new \Datetime('-31 days'));
        $this->assertEquals(31, $bundle->getDaysSinceLastCommit());
    }
}
