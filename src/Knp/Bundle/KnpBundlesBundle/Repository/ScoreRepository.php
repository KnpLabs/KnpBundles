<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Score;

/**
 * ScoreRepository
 *
 */
class ScoreRepository extends EntityRepository
{
    /**
     * Set the score value for a given date and bundle.
     * If the entry does not yet exist in DB, create a new object
     * (you're responsible for persisting it)
     *
     * @param DateTime $date
     * @param Bundle $bundle
     * @param int Value of the score
     * @return Score
     */
    public function setScore(\DateTime $date, Bundle $bundle, $value)
    {
        $score = new Score();
        $score->setBundle($bundle);
        $score->setDate($date);
        $score->setValue($value);
        
        // Score details
        $details = $bundle->getScoreDetails();
        $score->setFollowers(isset($details['followers']) ? $details['followers'] : 0);
        $score->setActivity(isset($details['activity']) ? $details['activity'] : 0);
        $score->setReadme(isset($details['readme']) ? $details['readme'] : 0);
        $score->setTravisci(isset($details['travisci']) ? $details['travisci'] : 0);
        $score->setTravisbuild(isset($details['travisbuild']) ? $details['travisbuild'] : 0);
        $score->setComposer(isset($details['composer']) ? $details['composer'] : 0);
        $score->setRecommenders(isset($details['recommenders']) ? $details['recommenders'] : 0);


        return $score;
    }

    /**
     * Finds the Score object for a given date and bundle
     *
     * @param DateTime $date
     * @param Bundle $bundle
     * @return Score or null
     */
    public function findOneByDateAndBundle(\DateTime $date, Bundle $bundle)
    {
        try {
            return $this->createQueryBuilder('s')
                ->where('s.bundle = :bundle_id')
                ->andWhere('s.date = :date')
                ->setParameter('bundle_id', $bundle->getId())
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

    /**
     * Get an array containing dates and number of scores for this date
     *
     * @return array
     */
    public function getScoreCountEvolution()
    {

        return $this->createQueryBuilder('s')
            ->select('s.date, COUNT(s.id) AS number')
            ->groupBy('s.date')
            ->orderBy('s.date', 'asc')
            ->getQuery()
            ->execute();
    }
}
