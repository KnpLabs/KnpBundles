<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\EventListener\Scoring\ActivityListener;

class ActivityListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testActivityScoreDetail()
    {
        $bundle = new Bundle();
        $bundle->setLastCommitAt(new \DateTime('-10days'));

        $tester = new ActivityListener();
        $tester->updateScore($bundle);
        $bundle->recalculateScore();

        $this->assertEquals(4, $bundle->getScore());

        $oldBundle = new Bundle();
        $oldBundle->setLastCommitAt(new \DateTime('-45days'));

        $tester->updateScore($oldBundle);
        $oldBundle->recalculateScore();

        $this->assertEquals(0, $oldBundle->getScore());
    }
}