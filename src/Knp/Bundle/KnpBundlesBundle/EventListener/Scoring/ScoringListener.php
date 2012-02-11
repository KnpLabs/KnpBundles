<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Event\BundleEvent;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
abstract class ScoringListener
{
    
    /**
     * Handles a Bundle::UPDATE_SCORE event so the listener 
     * can update the score (add its own scoring detail : travis, readme...)
     *
     * @param Knp\Bundle\KnpBundlesBundle\EventDispatcher\BundleEvent
     */
    public function onScoreUpdate(BundleEvent $event)
    {
        $this->updateScore($event->getBundle());
    }

    abstract public function updateScore(Bundle $bundle);

}