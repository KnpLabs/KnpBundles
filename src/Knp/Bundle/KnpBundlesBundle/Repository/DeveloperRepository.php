<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DeveloperRepository
 */
class DeveloperRepository extends OwnerRepository
{
    public function findAllNameOnly()
    {
        return $this->createQueryBuilder('d')
            ->select('d.name')
            ->getQuery()
            ->getResult()
        ;
    }

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
}
