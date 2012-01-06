<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Common\Cache\ApcCache;

class BundleRepository extends EntityRepository
{
    public function search($query)
    {
        $pattern = '%'.str_replace(' ', '%', $query).'%';

        $qb = $this->createQueryBuilder('e')->orderBy('e.score', 'DESC');
        $qb->where($qb->expr()->orx(
            $qb->expr()->like('e.username', ':username'),
            $qb->expr()->like('e.name', ':name'),
            $qb->expr()->like('e.description', ':description')
        ));
        $qb->setParameters(array('username' => $pattern, 'name' => $pattern, 'description' => $pattern));
        return $qb->getQuery()->execute();
    }

    public function findAllSortedBy($field, $nb = null)
    {
        $query = $this->queryAllSortedBy($field);

        if(null !== $nb) {
            $query->setMaxResults($nb);
        }

        return $query->execute();
    }

    public function queryAllSortedBy($field)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->orderBy('e.'.$field, 'name' === $field ? 'asc' : 'desc');
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
            ->getQuery()

            // cache query
            ->setResultCacheDriver(new ApcCache())
            ->setResultCacheLifetime(3600)
        ;

        return $q;
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(e.id) FROM '.$this->getEntityName().' e')->getSingleScalarResult();
    }

    public function getLastCommits($nb)
    {
        $bundles = $this->findByLastCommitAt($nb);
        $commits = array();
        foreach($bundles as $bundle) {
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
        return $this->createQueryBuilder('b')->orderBy('b.lastCommitAt', 'DESC')->getQuery()->setMaxResults($nb)->execute();
    }

    public function findOneByUsernameAndName($username, $name)
    {
        try {
            return $this->createQueryBuilder('e')
                ->leftJoin('e.recommenders', 'user')
                ->where('e.username = :username')
                ->andWhere('e.name = :name')
                ->setParameter('username', $username)
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleResult();
        } catch(NoResultException $e) {
            return null;
        }
    }

    public function updateTrends()
    {
        // Reset trends
        $q = $this->_em->createQuery('UPDATE Knp\Bundle\KnpBundlesBundle\Entity\Bundle b SET b.trend1 = 0');
        $q->execute();

        // Update trends.
        // TODO: Improve me
        $sql = <<<EOF
UPDATE bundle
JOIN
  (SELECT bundle_id, value AS startScore
    FROM score
    WHERE
      date = CURRENT_DATE - 1
      ) startRange
  ON startRange.bundle_id = bundle.id
  JOIN
  (SELECT bundle_id, value AS endScore
    FROM score
    WHERE
      date = CURRENT_DATE
      AND value >= 15
    ) endRange
  ON startRange.bundle_id = endRange.bundle_id
SET trend1 = (endScore - startScore)
WHERE description != '';
EOF;
        $conn = $this->_em->getConnection();
        $nbRows = $conn->executeUpdate($sql);

        return $nbRows;
    }

    public function findLatestSortedBy($field)
    {
        $query = $this->queryAllSortedBy($field);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
}
