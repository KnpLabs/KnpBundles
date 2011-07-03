<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector\Criterion;

use Knp\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * Interface that must be implemented by the criteria
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
 */
interface CriterionInterface
{
    /**
     * Indicates whether the given Repo meets the criterion
     *
     * @param  Repo $repo A Repo instance
     *
     * @return Boolean
     */
    function matches(Repo $repo);
}
