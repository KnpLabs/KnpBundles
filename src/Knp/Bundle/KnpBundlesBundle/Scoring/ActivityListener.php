<?php

namespace Knp\Bundle\KnpBundlesBundle\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class ActivityListener extends ScoringListener
{
    
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('activity', $bundle->getDaysSinceLastCommit() < 30
            ? (30 - $bundle->getDaysSinceLastCommit()) / 5
            : 0);
    }

}