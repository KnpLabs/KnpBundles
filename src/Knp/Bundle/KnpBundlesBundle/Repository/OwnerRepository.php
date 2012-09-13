<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function getEvolutionCounts($period = 50)
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Knp\Bundle\KnpBundlesBundle\Entity\Score', 'e');
        $rsm->addFieldResult('e', 'id', 'id');
        $rsm->addFieldResult('e', 'date', 'date');
        $rsm->addFieldResult('e', 'value', 'value');

        $sql = <<<EOF
SELECT id, createdAt, DATE(createdAt) as date,
    (SELECT COUNT(id) FROM owner WHERE DATE(createdAt) = date AND discriminator = :entityType) as value
FROM owner
WHERE createdAt > :period
  AND discriminator = :entityType
GROUP BY date
ORDER BY date ASC
EOF;

        $periodDate = new \DateTime(sprintf('%d days ago', $period));
        $periodDate = $periodDate->format('Y-m-d H:i:s');

        $entityType = strstr($this->getEntityName(), 'Developer') ? 'developer' : 'organization';

        return $this
            ->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('period', $periodDate)
            ->setParameter('entityType', $entityType)
            ->getResult()
        ;
    }

    public function count()
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(e.id) FROM '.$this->getEntityName().' e')->getSingleScalarResult();
    }
}
