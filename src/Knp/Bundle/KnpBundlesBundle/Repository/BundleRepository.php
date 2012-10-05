<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

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

        return $qb->getQuery();
    }

    /**
     * Finds all the bundles with their associated owners and contributors, sorted
     * by the specified field
     *
     * @param  string $field The name of the field to sort by
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findAllWithOwnersAndContributorsSortedBy($field)
    {
        return $this->queryAllWithOwnersAndContributorsSortedBy($field)->execute();
    }

    /**
     * Returns the query to retrieve all the bundles with their associated owners
     * and contributors, sorted by the specified field
     *
     * @param  string $field The name of the field to sort by
     *
     * @return \Doctrine\ORM\Query
     */
    public function queryAllWithOwnersAndContributorsSortedBy($field)
    {
        $q = $this->createQueryBuilder('bundle')
            ->select('bundle, owner, contributors')
            ->leftJoin('bundle.owner', 'owner')
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
            ->addSelect('owner')
            ->leftJoin('bundle.owner', 'owner')
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

    public function findByLastCommitAt($nb)
    {
        return $this->createQueryBuilder('bundle')->orderBy('bundle.lastCommitAt', 'DESC')->getQuery()->setMaxResults($nb)->execute();
    }

    public function findOneByOwnerNameAndName($ownerName, $name)
    {
        return $this->createQueryBuilder('bundle')
            ->leftJoin('bundle.recommenders', 'owner')
            ->where('bundle.ownerName = :ownerName')
            ->andWhere('bundle.name = :name')
            ->setParameter('ownerName', $ownerName)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getStaleBundlesForIndexing()
    {
        return $this->createQueryBuilder('bundle')
            ->leftJoin('bundle.owner', 'owner')
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

    public function getEvolutionCounts($period = 50)
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Knp\Bundle\KnpBundlesBundle\Entity\Score', 'e');
        $rsm->addFieldResult('e', 'id', 'id');
        $rsm->addFieldResult('e', 'date', 'date');
        $rsm->addFieldResult('e', 'value', 'value');

        $sql = <<<EOF
SELECT id, COUNT(id) as `value`, DATE(createdAt) as `date`
FROM bundle
WHERE createdAt > :period
GROUP BY `date`
ORDER BY `date` ASC
EOF;

        $periodDate = new \DateTime(sprintf('%d days ago', $period));
        $periodDate = $periodDate->format('Y-m-d H:i:s');

        return $this
            ->getEntityManager()
            ->createNativeQuery($sql, $rsm)
            ->setParameter('period', $periodDate)
            ->getResult()
        ;
    }
}
