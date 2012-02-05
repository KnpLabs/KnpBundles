<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Scoring\ComposerListener;

class ComposerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testComposerScoreDetail()
    {
        $bundle = new Bundle();
        $bundle->setComposerName(null);

        $tester = new ComposerListener();
        
        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        $this->assertEquals(0, $bundle->getScore());

        $bundle->setComposerName('composer-name-bundle');
        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        $this->assertEquals(5, $bundle->getScore());
    }
}