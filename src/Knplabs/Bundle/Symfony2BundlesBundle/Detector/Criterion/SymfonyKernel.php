<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Detector\Criterion;

use Symfony\Component\Finder\Finder;
use Knplabs\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * Criterion that checks if the repository contains a Symfony kernel
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
class SymfonyKernel implements CriterionInterface
{
    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        $directory = $repo->getGitRepo()->getDir();

        $finder = new Finder();
        $finder->files()->name('*Kernel.php')->in($directory)->depth('< 3');

        $candidates = $finder->getIterator();

        foreach($candidates as $candidate) {
            if(preg_match('/public function registerBundles/s', file_get_contents($candidate))) {
                return true;
            }
        }

        return false;
    }
}
