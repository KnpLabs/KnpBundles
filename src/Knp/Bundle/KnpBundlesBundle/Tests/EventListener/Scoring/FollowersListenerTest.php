<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\EventListener\Scoring\FollowersListener;

class FollowersListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideFollowers
     */
    public function testFollowersScoreDetail($followers)
    {
        $bundle = new Bundle();
        $bundle->setNbFollowers($followers);

        $tester = new FollowersListener();
        $tester->updateScore($bundle);
        $bundle->recalculateScore();  

        // 1 follower = 1 point
        $this->assertEquals($followers, $bundle->getScore());
    }

    public function provideFollowers()
    {
        return array(array(0), array(300));
    }
}