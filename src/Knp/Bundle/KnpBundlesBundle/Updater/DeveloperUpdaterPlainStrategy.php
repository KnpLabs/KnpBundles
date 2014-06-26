<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;
use Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdaterPlain;

class DeveloperUpdaterPlainStrategy implements DeveloperUpdaterStrategyInterface
{
    protected $developerUpdaterPlain;

    public function __construct(DeveloperUpdaterPlain $developerUpdaterPlain)
    {
        $this->developerUpdaterPlain = $developerUpdaterPlain;
    }

    public function updateDeveloperByName($name)
    {
        $this->developerUpdaterPlain->updateByName($name);
    }
}
