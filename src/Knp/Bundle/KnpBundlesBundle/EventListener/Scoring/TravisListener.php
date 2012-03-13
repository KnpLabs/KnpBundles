<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it evaluates a bundle based on 
 * the use of travis (http://travis-ci.org/) and the build status.
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
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