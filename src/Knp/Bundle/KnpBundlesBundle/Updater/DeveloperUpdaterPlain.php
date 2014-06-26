<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\KnpBundlesBundle\Repository\DeveloperRepository;
use Knp\Bundle\KnpBundlesBundle\Github\Developer as GithubDeveloper;

class DeveloperUpdaterPlain
{
    protected $entityManager;
    protected $developerRepository;
    protected $githubDeveloper;

    public function __construct(
        EntityManager $entityManager,
        DeveloperRepository $developerRepository,
        GithubDeveloper $githubDeveloper
    ) {
        $this->entityManager = $entityManager;
        $this->developerRepository = $developerRepository;
        $this->githubDeveloper = $githubDeveloper;
    }

    public function updateByName($name)
    {
        $developer = $this->developerRepository->findOneByName($name);
        if ($developer) {
            $this->githubDeveloper->update($developer);
            $this->entityManager->flush($developer);
        }
    }
}
