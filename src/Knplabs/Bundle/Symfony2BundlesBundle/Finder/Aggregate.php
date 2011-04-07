<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Finder;

/**
 * Aggregate finder. It handles some finders and uses each of them to find the
 * repositories
 *
 * @package Symfony2Bundles
 */
class Aggregate implements FinderInterface
{
    private $finders;

    /**
     * Constructor
     *
     * @param  array $finders An optional array of finders
     */
    public function __construct(array $finders = array())
    {
        $this->setFinders($finders);
    }

    /**
     * Defines the finders. It clears the current finders list
     *
     * @param  array $finders An array of finders
     */
    public function setFinders(array $finders)
    {
        $this->finders = new \SplObjectStorage();
        foreach ($finders as $finder) {
            $this->addFinder($finder);
        }
    }

    /**
     * Adds a finder
     *
     * @param  FinderInterface $finder
     */
    public function addFinder(FinderInterface $finder)
    {
        $this->finders->attach($finder);
    }

    /**
     * {@inheritDoc}
     */
    public function find()
    {
        $repositories = array();
        foreach ($this->finders as $finder) {
            $repositories = array_merge(
                $repositories,
                $finder->find()
            );
        }

        return array_unique($repositories);
    }
}
