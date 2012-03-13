<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it evaluates a bundle based on the use
 * composer.
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class ComposerListener extends ScoringListener
{
    /**
     * {@inheritdoc}
     */
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('composer', $bundle->getComposerName() ? 5 : 0);
    }
}