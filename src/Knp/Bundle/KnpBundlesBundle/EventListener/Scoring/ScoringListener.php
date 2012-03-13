<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Event\BundleEvent;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it provides the 2 basics methods
 * to evaluate a bundle. All scoring listeners must extend this class.
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
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

    /**
     * Add details to bundle's score
     *
     * @param Knp\Bundle\KnpBundlesBundle\Entity\Bundle
     */
    abstract public function updateScore(Bundle $bundle);

}