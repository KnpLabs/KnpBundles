<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\KnpBundlesBundle\Producer\RabbitProducer;
use Knp\Bundle\KnpBundlesBundle\Repository\DeveloperRepository;

use Knp\Bundle\KnpBundlesBundle\Github\Developer as GithubDeveloper;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper;

class DeveloperUpdater
{
    protected $updateDeveloperProducer;
    protected $entityManager;
    protected $developerRepository;
    protected $githubDeveloper;

    protected $messenger;

    public function __construct(
        RabbitProducer $updateDeveloperProducer,
        EntityManager $entityManager,
        DeveloperRepository $developerRepository,
        GithubDeveloper $githubDeveloper
    ) {
        $this->updateDeveloperProducer = $updateDeveloperProducer;
        $this->entityManager = $entityManager;
        $this->developerRepository = $developerRepository;
        $this->githubDeveloper = $githubDeveloper;

        $this->messenger = function($name){};
    }

    public function updateAll()
    {
        $developers = $this->developerRepository->findAllNameOnly();
        foreach ($developers as $developer) {
            $this->publishUpdateMessage($developer['name']);
        }
    }

    public function updateDeveloperByName($developerName)
    {
        $developer = $this->developerRepository->findOneByName($developerName);
        if ($developer) {
            $this->updateDeveloper($developer);
        }
    }

    public function updateDeveloper(EntityDeveloper $developer)
    {
        $name = $developer->getName();
        $this->publishUpdateMessage($name);
    }

    public function performDeveloperUpdate($developerName)
    {
        $developer = $this->developerRepository->findOneByName($developerName);
        if ($developer) {
            $this->githubDeveloper->update($developer);
            $this->entityManager->flush($developer);
        }
    }

    public function setMessenger(\Closure $messenger)
    {
        $this->messenger = $messenger;
    }

    protected function publishUpdateMessage($name)
    {
        $this->updateDeveloperProducer->publish(json_encode(array('name' => $name)));

        // can not just use `$this->messenger($name)`
        // cause this will be call of nonexistent method
        $this->messenger->__invoke($name);
    }
}
