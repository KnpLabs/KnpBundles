<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector;

class Bundle extends Detector
{
    public function __construct()
    {
        $criterion = new Criterion\RepoNameRegExp('/^(.+)Bundle$/');

        parent::__construct($criterion);
    }
}
