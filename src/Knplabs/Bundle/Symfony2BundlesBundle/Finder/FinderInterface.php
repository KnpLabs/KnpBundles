<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Finder;

/**
 * Interface that must be implemented by the repository finders
 *
 * @package Symfony2Bundles
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
