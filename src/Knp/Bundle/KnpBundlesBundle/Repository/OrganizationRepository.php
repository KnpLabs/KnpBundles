<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * OrganizationRepository
 */
class OrganizationRepository extends OwnerRepository
{
    public function findOneByNameWithRepos($name)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.bundles', 'b')
            ->where('d.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getEvolutionCounts()
    {
        return $this->createQueryBuilder('o')
            ->select('o.createdAt AS date, COUNT(o.id) AS value')
            ->groupBy('o.createdAt')
            ->orderBy('o.createdAt', 'asc')
            ->getQuery()
            ->execute();
    }

    public function queryAllWithBundlesSortedBy($field)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->leftJoin('u.bundles', 'b')
        ;

        switch ($field) {
            case 'name':
                $qb->orderBy('u.name', 'asc');
                break;

            case 'bundles':
                $qb
                    ->addSelect('SIZE(u.bundles) bundles')
                    ->orderBy('bundles', 'desc');
                break;

            case 'developers':
                $qb
                    ->addSelect('SIZE(u.members) members')
                    ->orderBy('members', 'desc');
                break;
        }

        return $qb->getQuery();
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(e.id) FROM '.$this->getEntityName().' e')->getSingleScalarResult();
    }
}
