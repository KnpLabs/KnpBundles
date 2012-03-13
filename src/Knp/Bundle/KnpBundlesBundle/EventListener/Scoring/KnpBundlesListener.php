<?php 

namespace Knp\Bundle\KnpBundlesBundle\EventListener\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * This is part of the scoring algorithm, it evaluates a bundle based on 
 * KNPBundles-specific criterias (number of recommenders...)
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class KnpBundlesListener extends ScoringListener
{
    /**
     * {@inheritdoc}
     */
    public function updateScore(Bundle $bundle)
    {
        $bundle->addScoreDetail('recommenders', 5 * $bundle->getNbRecommenders());
    }
}