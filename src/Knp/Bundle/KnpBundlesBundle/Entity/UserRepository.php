<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * UserRepository
 *
 */
class UserRepository extends EntityRepository
{

    public function findOneByName($name)
    {
        try {
            return $this->createQueryBuilder('u')
                ->where('u.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleResult();
        } catch(NoResultException $e) {
            return null;
        }
    }

    public function findOneByNameWithRepos($name)
    {
        try {
            return $this->createQueryBuilder('u')
                ->leftJoin('u.bundles', 'b')
                ->leftJoin('u.contributionBundles', 'cr')
                ->where('u.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleResult();
        } catch(NoResultException $e) {
            return null;
        }
    }

    public function findAllSortedBy($field)
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.'.$field, 'name' === $field ? 'asc' : 'desc')
            ->getQuery()
            ->getResult();
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
}
