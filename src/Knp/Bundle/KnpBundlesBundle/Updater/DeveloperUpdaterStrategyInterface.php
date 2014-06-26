<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

interface DeveloperUpdaterStrategyInterface
{
    public function updateDeveloperByName($name);
}
