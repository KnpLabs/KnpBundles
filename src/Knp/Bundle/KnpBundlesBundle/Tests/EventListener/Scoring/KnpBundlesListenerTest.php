<?php 

namespace Knp\Bundle\KnpBundlesBundle\Tests\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\User;
use Knp\Bundle\KnpBundlesBundle\EventListener\Scoring\KnpBundlesListener;

/**
* 
*/
class KnpBundlesListenerTest extends \PHPUnit_Framework_TestCase
{
    
    public function testRecommandationsScoreUpdate()
    {
        $bundle = new Bundle();
        for ($index = 0; $index < 5; ++$index) {
            $user = new User();
            $user->setName('Contributor #'.($index + 1));
            $bundle->addRecommender($user);
        }

        $tester = new KnpBundlesListener();
        $tester->updateScore($bundle);
        $bundle->recalculateScore();

        $this->assertEquals(25, $bundle->getScore());
    }

}