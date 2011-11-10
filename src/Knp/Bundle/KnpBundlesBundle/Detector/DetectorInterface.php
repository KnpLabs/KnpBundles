<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector;

use Knp\Bundle\KnpBundlesBundle\Git\Repo;

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
