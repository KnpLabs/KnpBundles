<?php

namespace Knp\Bundle\KnpBundlesBundle\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
* 
*/
class ReadmeListener extends ScoringListener
{

    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('readme', mb_strlen($bundle->getReadme()) > 300 ? 5 : 0);
    }

}