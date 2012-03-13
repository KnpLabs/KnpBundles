<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it evaluates a bundle based on the activity
 * (number of commits in the past x days...)
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class ActivityListener extends ScoringListener
{
    /**
     * {@inheritdoc}
     */
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('activity', $bundle->getDaysSinceLastCommit() < 30
            ? (30 - $bundle->getDaysSinceLastCommit()) / 5
            : 0);
    }
}