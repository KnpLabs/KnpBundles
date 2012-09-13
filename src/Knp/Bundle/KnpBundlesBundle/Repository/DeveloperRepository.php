<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DeveloperRepository
 */
class DeveloperRepository extends OwnerRepository
{
    public function findOneByNameWithRepos($name)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.bundles', 'b')
            ->leftJoin('d.contributionBundles', 'cr')
            ->where('d.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllWithBundlesSortedBy($field)
    {
        return $this->queryAllWithBundlesSortedBy($field)->getResult();
    }

    public function queryAllWithBundlesSortedBy($field)
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.'.$field, 'name' === $field ? 'asc' : 'desc')
            ->leftJoin('d.bundles', 'b')
            ->leftJoin('d.contributionBundles', 'cr')
            ->select('d, b, cr')
            ->getQuery();
    }

    public function getEvolutionCounts()
    {
        return $this->createQueryBuilder('d')
            ->select('d.createdAt AS date, COUNT(d.id) AS value')
            ->groupBy('d.createdAt')
            ->orderBy('d.createdAt', 'asc')
            ->getQuery()
            ->execute();
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(e.id) FROM '.$this->getEntityName().' e')->getSingleScalarResult();
    }
}
