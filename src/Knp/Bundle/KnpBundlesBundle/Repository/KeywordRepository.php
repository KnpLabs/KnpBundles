<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Knp\Bundle\KnpBundlesBundle\Entity\Keyword;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class KeywordRepository extends EntityRepository
{
    /**
     * Find keyword with given value or create new one
     * 
     * @return Keyword
     */
    public function findOrCreateOne($value)
    {
        $keyword = $this->findOneByValue($value);

        if (!$keyword) {
            $keyword = new $this->_class->name;
            $keyword->setValue($value);
        }

        return $keyword;
    }
}
