<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

/**
 * Interface that must be implemented by the repository finders
 *
 * @package KnpBundles
 */
interface FinderInterface
{
    /**
     * Finds the repositories
     *
     * @return array
     */
    function find();
}
