<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ScoreRepository
 */
class ScoreRepository extends EntityRepository
{
    /**
     * Get an array containing dates and sum of scores for this date
     *
     * @return array
     */
    public function getSumEvolution()
    {
        return $this->createQueryBuilder('s')
            ->select('s.date, SUM(s.value) AS sumValues')
            ->groupBy('s.date')
            ->orderBy('s.date', 'asc')
            ->getQuery()
            ->execute();
    }

    /**
     * Get an array containing dates and number of scores for this date
     *
     * @return array
     */
    public function getEvolutionCounts($period = 50)
    {
        return $this->createQueryBuilder('s')
            ->select('s.date, COUNT(s.id) AS value')
            ->where('s.date > :period')
            ->groupBy('s.date')
            ->orderBy('s.date', 'asc')
            ->setParameter('period', new \DateTime(sprintf('%d days ago', $period)))
            ->getQuery()
            ->execute();
    }
}
