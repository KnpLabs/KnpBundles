<?php

namespace Knp\Bundle\KnpBundlesBundle\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class TravisListener extends ScoringListener
{
    
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('travisci', $bundle->getUsesTravisCi() ? 5 : 0);
        $bundle->addScoreDetail('travisbuild', $bundle->getTravisCiBuildStatus() ? 5 : 0);
    }
}