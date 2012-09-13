<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * OwnerRepository
 */
class OwnerRepository extends EntityRepository
{
    public function findOneByName($name)
    {
        return $this->createQueryBuilder('d')
            ->where('d.name = :name')
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
}
