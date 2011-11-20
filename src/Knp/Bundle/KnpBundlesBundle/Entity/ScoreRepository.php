<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * ScoreRepository
 *
 */
class ScoreRepository extends EntityRepository
{
    /**
     * Set the score value for a given date and repo.
     * If the entry does not yet exist in DB, create a new object
     * (you're responsible for persisting it)
     *
     * @param DateTime $date
     * @param Repo $repo
     * @param int Value of the score
     * @return Score
     */
    public function setScore(\DateTime $date, Repo $repo, $value)
    {
        $score = $this->findOneByDateAndBundle($date, $repo);
        if (!$score) {
            $score = new Score();
            $score->setRepo($repo);
            $score->setDate($date);
        }
        $score->setValue($value);

        return $score;
    }

    /**
     * Finds the Score object for a given date and repo
     *
     * @param DateTime $date
     * @param Repo $repo
     * @return Score or null
     */
    public function findOneByDateAndBundle(\DateTime $date, Repo $repo)
    {
        try {
            return $this->createQueryBuilder('s')
                ->where('s.repo = :repo_id')
                ->andWhere('s.date = :date')
                ->setParameter('repo_id', $repo->getId())
                ->setParameter('date', $date->format('Y-m-d'))
                ->getQuery()
                ->getSingleResult();
        } catch(NoResultException $e) {
            return null;
        }
    }

    /**
     * Get an array containing dates and sum of scores for this date
     *
     * @return array
     */
    public function getScoreSumEvolution()
    {
        $qb = $this->createQueryBuilder('s');

        $qb->select('s.date, SUM(s.value) AS sumValues')
        ->groupBy('s.date')
        ->orderBy('s.date', 'asc');

        $q = $qb->getQuery();

        return $q->execute();
    }
}
