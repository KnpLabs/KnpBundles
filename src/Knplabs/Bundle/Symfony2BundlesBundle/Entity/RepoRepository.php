<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class RepoRepository extends EntityRepository
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
     * Finds all the repos with their associated users and contributors, sorted
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
     * Returns the query to retrieve all the repos with their associated users
     * and contributors, sorted by the specified field
     *
     * @param  string $field The name of the field to sort by
     *
     * @return \Doctrine\Query
     */
    public function queryAllWithUsersAndContributorsSortedBy($field)
    {
        return $this->createQueryBuilder('repo')
            ->select('repo, user, contributors')
            ->leftJoin('repo.user', 'user')
            ->leftJoin('repo.contributors', 'contributors')
            ->orderBy('repo.' . $field, 'name' === $field ? 'asc' : 'desc')
            ->getQuery();
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(e.id) FROM '.$this->getEntityName().' e')->getSingleScalarResult();
    }

    public function getLastCommits($nb)
    {
        $repos = $this->findByLastCommitAt($nb);
        $commits = array();
        foreach($repos as $repo) {
            $commits = array_merge($commits, $repo->getLastCommits());
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
}
