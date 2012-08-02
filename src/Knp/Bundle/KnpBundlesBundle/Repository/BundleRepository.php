<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class BundleRepository extends EntityRepository
{
    public function findAllSortedBy($field, $order = 'desc', $nb = null)
    {
        $query = $this->queryAllSortedBy($field, $order);

        if (null !== $nb) {
            $query->setMaxResults($nb);
        }

        return $query->execute();
    }

    public function queryAllSortedBy($field, $order = 'desc')
    {
        $qb = $this->createQueryBuilder('b');
        $qb->orderBy('b.' . $field, $order);
        $query = $qb->getQuery();

        return $query;
    }

    /**
     * Finds all the bundles with their associated users and contributors, sorted
     * by the specified field
     *
     * @param  string $field The name of the field to sort by
     *
     * @return \Doctrine\Common\Collection
     */
    public function findAllWithUsersAndContributorsSortedBy($field)
    {
        return $this->queryAllWithUsersAndContributorsSortedBy($field)->execute();
    }

    /**
     * Returns the query to retrieve all the bundles with their associated users
     * and contributors, sorted by the specified field
     *
     * @param  string $field The name of the field to sort by
     *
     * @return \Doctrine\Query
     */
    public function queryAllWithUsersAndContributorsSortedBy($field)
    {
        $q = $this->createQueryBuilder('bundle')
            ->select('bundle, user, contributors')
            ->leftJoin('bundle.user', 'user')
            ->leftJoin('bundle.contributors', 'contributors')
            ->addOrderBy('bundle.' . $field, 'name' === $field ? 'asc' : 'desc')
            ->addOrderBy('bundle.score', 'desc')
            ->addOrderBy('bundle.lastCommitAt', 'desc')
            ->getQuery();

        return $q;
    }

    public function queryByKeywordSlug($slug)
    {
        return $this->createQueryBuilder('bundle')
            ->addSelect('user')
            ->leftJoin('bundle.user', 'user')
            ->leftJoin('bundle.keywords', 'keyword')
            ->where('keyword.slug = :slug')
            ->addOrderBy('bundle.score', 'desc')
            ->addOrderBy('bundle.lastCommitAt', 'desc')
            ->setParameter('slug', $slug)
            ->getQuery();
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(bundle.id) FROM ' . $this->getEntityName() . ' bundle')->getSingleScalarResult();
    }

    public function getLastCommits($nb)
    {
        $bundles = $this->findByLastCommitAt($nb);
        $commits = array();
        foreach ($bundles as $bundle) {
            $commits = array_merge($commits, $bundle->getLastCommits());
        }
        usort($commits, function($a, $b)
        {
            return strtotime($a['committed_date']) < strtotime($b['committed_date']);
        });
        $commits = array_slice($commits, 0, $nb);

        return $commits;
    }

    public function findByLastCommitAt($nb)
    {
        return $this->createQueryBuilder('bundle')->orderBy('bundle.lastCommitAt', 'DESC')->getQuery()->setMaxResults($nb)->execute();
    }

    public function findOneByUsernameAndName($username, $name)
    {
        try {
            return $this->createQueryBuilder('bundle')
                ->leftJoin('bundle.recommenders', 'user')
                ->where('bundle.username = :username')
                ->andWhere('bundle.name = :name')
                ->setParameter('username', $username)
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function getStaleBundlesForIndexing()
    {
        return $this->createQueryBuilder('bundle')
            ->leftJoin('bundle.user', 'user')
            ->where('bundle.indexedAt IS NULL OR bundle.indexedAt < bundle.updatedAt')
            ->getQuery()
            ->getResult();
    }

    public function findLatestTrend($idlePeriod)
    {
        return $this->createQueryBuilder('bundle')
            ->where('bundle.score > 0')
            ->andWhere('bundle.lastTweetedAt < :date or bundle.lastTweetedAt is null')
            ->addOrderBy('bundle.trend1', 'desc')
            ->setMaxResults(1)
            ->setParameter('date', new \DateTime(sprintf('-%s day', $idlePeriod)))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestSortedBy($field)
    {
        $query = $this->queryAllSortedBy($field);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
}
