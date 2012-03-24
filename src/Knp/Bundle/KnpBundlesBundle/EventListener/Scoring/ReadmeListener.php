<?php

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it evaluates a bundle based on 
 * how big the README file is.
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class ReadmeListener extends ScoringListener
{
    /**
     * {@inheritdoc}
     */
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('readme', mb_strlen($bundle->getReadme()) > 300 ? 5 : 0);
    }
}