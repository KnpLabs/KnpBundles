<?php 

namespace Knp\Bundle\KnpBundlesBundle\Tests\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer;
use Knp\Bundle\KnpBundlesBundle\EventListener\Scoring\KnpBundlesListener;

/**
 * Test the knpbundles related scoring update part
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class KnpBundlesListenerTest extends \PHPUnit_Framework_TestCase
{
    
    public function testRecommandationsScoreUpdate()
    {
        $bundle = new Bundle();
        for ($index = 0; $index < 5; ++$index) {
            $user = new Developer();
            $user->setName('Contributor #'.($index + 1));
            $bundle->addRecommender($user);
        }

        $tester = new KnpBundlesListener();
        $tester->updateScore($bundle);
        $bundle->recalculateScore();

        $this->assertEquals(25, $bundle->getScore());
    }

}
