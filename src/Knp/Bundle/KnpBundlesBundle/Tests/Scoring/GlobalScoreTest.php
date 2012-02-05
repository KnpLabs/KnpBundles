<?php 

namespace Knp\Bundle\KnpBundlesBundle\Tests\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\User;
use Knp\Bundle\KnpBundlesBundle\EventDispatcher\BundleEvent;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
* 
*/
class GlobalScoreTest extends \PHPUnit_Framework_TestCase
{
    
    public function testGlobalScore()
    {
        $dispatcher = new EventDispatcher();

        $testers = array('Activity', 'Composer', 'Followers', 'KnpBundles', 'Readme', 'Travis');
        foreach ($testers as $testerClass) {
            $fqcn = sprintf('\\Knp\\Bundle\\KnpBundlesBundle\\Scoring\\%sListener', $testerClass);
            $tester = new $fqcn();
            $dispatcher->addListener(BundleEvent::UPDATE_SCORE, array($tester, 'onScoreUpdate'));
        }

        $bundle = new Bundle();

        // activity (+4)
        $bundle->setLastCommitAt(new \DateTime('-10days')); 

        // composer (+5)
        $bundle->setComposerName('bundle-composer-name');

        // followers (+10)
        $bundle->setNbFollowers(10);

        // recommendation (+5)
        $bundle->addRecommender(new User());

        // readme (+5)
        $bundle->setReadme(str_repeat('-', 500));

        // travis (+10)
        $bundle->setUsesTravisCi(true);
        $bundle->setTravisCiBuildStatus(true);

        $dispatcher->dispatch(BundleEvent::UPDATE_SCORE, new BundleEvent($bundle));
        $bundle->recalculateScore();
        $this->assertEquals(39, $bundle->getScore());
    }

}