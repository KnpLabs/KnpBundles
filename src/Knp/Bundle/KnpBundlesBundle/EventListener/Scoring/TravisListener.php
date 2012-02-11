<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class TravisListener extends ScoringListener
{
    /**
     * {@inheritdoc}
     */
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('travisci', $bundle->getUsesTravisCi() ? 5 : 0);
        $bundle->addScoreDetail('travisbuild', $bundle->getTravisCiBuildStatus() ? 5 : 0);
    }
}