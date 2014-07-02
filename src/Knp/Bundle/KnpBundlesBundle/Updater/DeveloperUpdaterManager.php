<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Knp\Bundle\KnpBundlesBundle\Repository\DeveloperRepository;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper;
use Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdaterPlain;
use Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdaterStrategyInterface;

class DeveloperUpdaterManager
{
    protected $developerRepository;
    protected $developerUpdaterPlain;

    protected $updateStrategy;
    protected $messenger;

    public function __construct(
        DeveloperRepository $developerRepository,
        DeveloperUpdaterPlain $developerUpdaterPlain
    ) {
        $this->developerRepository = $developerRepository;
        $this->developerUpdaterPlain = $developerUpdaterPlain;

        $this->messenger = function($name){};
    }

    public function updateAll()
    {
        $developers = $this->developerRepository->findAllNameOnly();
        foreach ($developers as $developer) {
            $this->updateDeveloperByName($developer['name']);
        }
    }

    public function updateDeveloper(EntityDeveloper $developer)
    {
        $this->updateDeveloperByName($developer->getName());
    }

    public function updateDeveloperByName($developerName)
    {
        $this->updateStrategy->updateDeveloperByName($developerName);

        // can not just use `$this->messenger($name)`
        // cause this will be call of nonexistent method
        $this->messenger->__invoke($developerName);
    }

    public function performDeveloperUpdate($name)
    {
        $this->developerUpdaterPlain->updateByName($name);
    }

    public function setUpdateStrategy(DeveloperUpdaterStrategyInterface $updateStrategy)
    {
        $this->updateStrategy = $updateStrategy;
    }

    public function setMessenger(\Closure $messenger)
    {
        $this->messenger = $messenger;
    }
}
