<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

/**
 * Interface that must be implemented by the repository finders
 */
interface FinderInterface
{
    /**
     * Finds the repositories
     *
     * @return array
     */
    public function find();
}
