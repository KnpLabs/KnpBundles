<?php

namespace Knp\Bundle\KnpBundlesBundle\Repository;

use Knp\Bundle\KnpBundlesBundle\Entity\Keyword;
use Doctrine\ORM\EntityRepository;

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
            $class = $this->getClassName();
            $keyword = new $class;
            $keyword->setValue($value);
        }

        return $keyword;
    }
}
