<?php 

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class KnpBundlesListener extends ScoringListener
{
    
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('recommenders', 5 * $bundle->getNbRecommenders());
    }
}