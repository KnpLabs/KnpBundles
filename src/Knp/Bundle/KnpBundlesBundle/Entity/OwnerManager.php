<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\NullOutput;

use Github\Client;

use Knp\Bundle\KnpBundlesBundle\Github\Developer as GithubDeveloper;
use Knp\Bundle\KnpBundlesBundle\Github\Organization as GithubOrganization;
use Knp\Bundle\KnpBundlesBundle\Github\OwnerInterface as GithubOwnerInterface;

/**
 * Manages user entities
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class OwnerManager
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $github;

    /**
     * @param ObjectManager $entityManager
     * @param Client        $github
     */
    public function __construct(ObjectManager $entityManager, Client $github)
    {
        $this->entityManager = $entityManager;
        $this->github        = $github;
    }

    /**
     * @param array $data
     *
     * @return null|Owner
     */
    public function findOwnerBy(array $data)
    {
        return $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Owner')->findOneBy($data);
    }

    /**
     * @param array $data
     *
     * @return null|Developer
     */
    public function findDeveloperBy(array $data)
    {
        return $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Developer')->findOneBy($data);
    }

    /**
     * @param array $data
     *
     * @return null|Organization
     */
    public function findOrganizationBy(array $data)
    {
        return $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Organization')->findOneBy($data);
    }

    /**
     * @param string $ownerName
     * @param string $entityType
     *
     * @return boolean|Owner
     */
    public function createOwner($ownerName, $entityType = 'developer')
    {
        $findBy = array('name' => $ownerName);

        if ('developer' == $entityType) {
            $owner = $this->findDeveloperBy($findBy);
        } elseif ('organization' == $entityType) {
            $owner = $this->findOrganizationBy($findBy);
        } else {
            // If owner is unknown yet, skip loading
            $owner = $this->findOwnerBy($findBy);
        }

        if (!$owner) {
            if (!$api = $this->getApiByOwnerName($ownerName)) {
                return false;
            }

            if (!$owner = $api->import($ownerName, false)) {
                return false;
            }

            $this->entityManager->persist($owner);
            $this->entityManager->flush();
        }

        return $owner;
    }

    /**
     * @param string $ownerName
     *
     * @return boolean|GithubOwnerInterface
     */
    public function getApiByOwnerName($ownerName)
    {
        $githubOwner = $this->github->api('user')->show($ownerName);

        if (!is_array($githubOwner) || !isset($githubOwner['type'])) {
            return false;
        }

        if ($githubOwner['type'] != 'Organization') {
            $api = new GithubDeveloper($this->github, new NullOutput());
        } else {
            $api = new GithubOrganization($this->github, new NullOutput());
            $api->setRepository($this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Organization'));
        }

        return $api;
    }
}
