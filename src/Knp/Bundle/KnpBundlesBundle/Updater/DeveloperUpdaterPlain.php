<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\KnpBundlesBundle\Repository\DeveloperRepository;
use Knp\Bundle\KnpBundlesBundle\Repository\OrganizationRepository;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper;
use Knp\Bundle\KnpBundlesBundle\Entity\Organization as EntityOrganization;
use Knp\Bundle\KnpBundlesBundle\Github\Developer as GithubDeveloper;
use Knp\Bundle\KnpBundlesBundle\Github\Organization as GithubOrganization;
use Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager;

class DeveloperUpdaterPlain
{
    protected $entityManager;
    protected $developerRepository;
    protected $organizationRepository;
    protected $githubDeveloper;
    protected $githubOrganization;

    public function __construct(
        EntityManager $entityManager,
        DeveloperRepository $developerRepository,
        OrganizationRepository $organizationRepository,
        GithubDeveloper $githubDeveloper,
        GithubOrganization $githubOrganization,
        OwnerManager $ownerManager
    ) {
        $this->entityManager = $entityManager;
        $this->developerRepository = $developerRepository;
        $this->organizationRepository = $organizationRepository;

        // Inject 'GithubDeveloper' and 'GithubOrganization' directly
        // instead of fetching them from 'OwnerManager' via 'getApiByOwnerName'
        // to avoid extra api call and bypass suggestion logic
        $this->githubDeveloper = $githubDeveloper;
        $this->githubOrganization = $githubOrganization;
        $this->githubOrganization->setOwnerManager($ownerManager);
    }

    public function updateByName($name)
    {
        $developer = $this->developerRepository->findOneByName($name);
        if ($developer) {
            $this->githubDeveloper->update($developer);
            $this->updateDeveloperOrganizations($developer);

            $this->entityManager->flush();
        }
    }

    protected function updateDeveloperOrganizations(EntityDeveloper $developer)
    {
        $organizationsData = $this->githubDeveloper->getOrganizations($developer);
        $developerOrganizations = $developer->getOrganizations();

        $developerOrganizationNames = array();
        foreach ($organizationsData as $organization) {
            $developerOrganizationNames[] = $organization['login'];
        }

        // Add missed organizations
        foreach ($developerOrganizationNames as $organizationName) {
            $isBelongTo = $developerOrganizations->exists(function($key, $element) use ($organizationName) {
                return $organizationName === $element->getName();
            });

            if ($isBelongTo) {
                continue;
            }

            if (!$organizationEntity = $this->organizationRepository->findOneByName($organizationName)) {
                $organizationEntity = $this->githubOrganization->import($organizationName, $update = true, $updateMembers = false);
                $this->entityManager->persist($organizationEntity);
            }
            $developer->addOrganization($organizationEntity);
        }

        // Remove extra organizations
        foreach ($developerOrganizations as $developerOrganization) {
            $extraOrganization = !in_array($developerOrganization->getName(), $developerOrganizationNames, true);
            if ($extraOrganization) {
                $developer->removeOrganization($developerOrganization);
            }
        }
    }
}
