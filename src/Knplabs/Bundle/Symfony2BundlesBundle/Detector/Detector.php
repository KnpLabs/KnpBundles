<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Detector;

use Knplabs\Bundle\Symfony2BundlesBundle\Detector\Criterion\CriterionInterface;
use Knplabs\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * Abstract detector class based on the criteria system
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
abstract class Detector implements DetectorInterface
{
    private $criterion;

    /**
     * Constructor
     *
     * @param  CriterionInterface $criterion A CriterionInterface instance
     */
    public function __construct(CriterionInterface $criterion)
    {
        $this->criterion = $criterion;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        return $this->criterion->matches($repo);
    }
}
