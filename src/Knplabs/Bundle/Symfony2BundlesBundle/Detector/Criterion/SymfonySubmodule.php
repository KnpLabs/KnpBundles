<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Detector\Criterion;

use Knplabs\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * Criterion that checks there is a Symfony submodule
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
class SymfonySubmodule implements CriterionInterface
{
    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        $directory  = $repo->getGitRepo()->getDir();
        $gitmodules = $directory . '/.gitmodules';

        return file_exists($gitmodules) && preg_match('/symfony/is', file_get_contents($gitmodules));
    }
}
