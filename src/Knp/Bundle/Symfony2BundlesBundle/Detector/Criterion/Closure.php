<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector\Criterion;

use Knp\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * Criterion that delegates the decision to the configured closure. The repo
 * will be passed to the closure which is responsible to return a boolean
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
 */
class Closure implements CriterionInterface
{
    private $closure;

    /**
     * Constructor
     *
     * @param  \Closure $closure A closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        $result = call_user_func($this->closure, $repo);
        if (!is_bool($result)) {
            throw new \RuntimeException('The configured closure did not return a boolean.');
        }

        return $result;
    }
}
