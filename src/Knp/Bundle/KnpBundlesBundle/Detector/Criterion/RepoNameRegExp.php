<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

use Knp\Bundle\KnpBundlesBundle\Git\Repo;

/**
 * Criterion that checks the repo's name matches the configured rexexp
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
 */
class RepoNameRegExp implements CriterionInterface
{
    private $pattern;

    /**
     * Constructor
     *
     * @param  string $pattern The pattern that the repo name must match
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        $name = $repo->getBundleEntity()->getName();

        return (Boolean) preg_match($this->pattern, $name);
    }
}
