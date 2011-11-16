<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Entity\Repo;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDaysSinceLastCommit()
    {
        $repo = Repo::create('knplabs/KnpMenuBundle');
        $repo->setLastCommitAt(new \Datetime('-31 days'));
        $this->assertEquals(31, $repo->getDaysSinceLastCommit());
    }
}
