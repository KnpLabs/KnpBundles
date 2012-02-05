<?php

namespace Knp\Bundle\KnpBundlesBundle\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class ComposerListener extends ScoringListener
{
    
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('composer', $bundle->getComposerName() ? 5 : 0);
    }
    
}