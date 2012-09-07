<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    public function findOneByName($name)
    {
        return $this->createQueryBuilder('u')
            ->where('u.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByNameWithRepos($name)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.bundles', 'b')
            ->leftJoin('u.contributionBundles', 'cr')
            ->where('u.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllSortedBy($field, $limit = null)
    {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.'.$field, 'name' === $field ? 'asc' : 'desc')
            ->getQuery();

        if (null !== $limit) {
            $query->setMaxResults($limit);
        }

        return $query->execute();
    }

    public function findAllWithBundlesSortedBy($field)
    {
        return $this->queryAllWithBundlesSortedBy($field)->getResult();
    }

    public function queryAllWithBundlesSortedBy($field)
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.'.$field, 'name' === $field ? 'asc' : 'desc')
            ->leftJoin('u.bundles', 'b')
            ->leftJoin('u.contributionBundles', 'cr')
            ->select('u, b, cr')
            ->getQuery();
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(e.id) FROM '.$this->getEntityName().' e')->getSingleScalarResult();
    }

    public function getUsersCountEvolution()
    {
        return $this->createQueryBuilder('u')
            ->select('u.createdAt AS date, COUNT(u.id) AS value')
            ->groupBy('u.createdAt')
            ->orderBy('u.createdAt', 'asc')
            ->getQuery()
            ->execute();
    }
}
