<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Scoring\TravisListener;

class TravisListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testUseTravisScoreDetail()
    {
        $bundle = new Bundle();
        $tester = new TravisListener();

        $bundle->setUsesTravisCi(false);
        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        $this->assertEquals(0, $bundle->getScore());

        $bundle->setUsesTravisCi(true);
        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        $this->assertEquals(5, $bundle->getScore());
    }

    public function testTravisBuildStatus()
    {
        $bundle = new Bundle();
        $tester = new TravisListener();

        $bundle->setTravisCiBuildStatus(false);
        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        $this->assertEquals(0, $bundle->getScore());

        $bundle->setTravisCiBuildStatus(true);
        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        $this->assertEquals(5, $bundle->getScore());
    }
}