<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Score;

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
    public function getScoreSumEvolution()
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
    public function getScoreCountEvolution()
    {
        return $this->createQueryBuilder('s')
            ->select('s.date, COUNT(s.id) AS value')
            ->groupBy('s.date')
            ->orderBy('s.date', 'asc')
            ->getQuery()
            ->execute();
    }
}
