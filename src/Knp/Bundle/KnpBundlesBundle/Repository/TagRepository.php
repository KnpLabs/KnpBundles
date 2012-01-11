<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class TagRepository extends EntityRepository
{
    public function findOneByName($name)
    {
        try {
            return $this->createQueryBuilder('t')
                ->where('t.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleResult();
        } catch(NoResultException $e) {
            return null;
        }
    }

    public function findOneBySluggedName($sluggedName)
    {
        try {
            return $this->createQueryBuilder('t')
            ->where('t.sluggedName = :sluggedName')
            ->setParameter('sluggedName', $sluggedName)
            ->getQuery()
            ->getSingleResult();
        } catch(NoResultException $e) {
            return null;
        }
    }

    public function findOrCreateOne($name)
    {
        $tag = $this->findOneByName($name);

        if (!$tag) {
            $tag = new $this->_class->name;
            $tag->setName($name);
        }

        return $tag;
    }
}
