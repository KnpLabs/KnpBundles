<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class FollowersListener extends ScoringListener
{
    
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('followers', $bundle->getNbFollowers());
    }
}