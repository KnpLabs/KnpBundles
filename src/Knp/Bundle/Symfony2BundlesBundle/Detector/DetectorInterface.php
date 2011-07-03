<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector;

use Knp\Bundle\Symfony2BundlesBundle\Git\Repo;

interface DetectorInterface
{
    /**
     * Indicates whether the given repo matches the detector's criteria
     *
     * @param Repo $repo
     *
     * @return Boolean
     */
    function matches(Repo $repo);
}
