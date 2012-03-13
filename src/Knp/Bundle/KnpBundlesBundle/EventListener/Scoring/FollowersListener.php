<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it evaluates a bundle based on how
 * many people are following the development on github
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class FollowersListener extends ScoringListener
{
    /**
     * {@inheritdoc}
     */
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('followers', $bundle->getNbFollowers());
    }
}