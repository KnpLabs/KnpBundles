<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

use Knp\Bundle\KnpBundlesBundle\Git\Repo;

/**
 * Criterion that checks there is a Symfony submodule
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
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
