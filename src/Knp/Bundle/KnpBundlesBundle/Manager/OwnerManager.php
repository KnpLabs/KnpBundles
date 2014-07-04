<?php

namespace Knp\Bundle\KnpBundlesBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\NullOutput;

use Github\Client;
use Github\Exception\RuntimeException;

use Knp\Bundle\KnpBundlesBundle\Entity\Developer;
use Knp\Bundle\KnpBundlesBundle\Entity\Organization;
use Knp\Bundle\KnpBundlesBundle\Entity\Owner;

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
        return $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Owner')->findOneByUniqueFields($data);
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
     * @param string  $ownerName
     * @param string  $entityType
     * @param boolean $flushEntities
     *
     * @return boolean|Owner
     */
    public function createOwner($ownerName, $entityType = 'developer', $flushEntities = true)
    {
        $findBy = array(
            'name'     => $ownerName,
            'githubId' => $ownerName,
        );

        if ('unknown' != $entityType) {
            $findBy['discriminator'] = $entityType;
        }

        $owner = $this->findOwnerBy($findBy);

        if (!$owner) {
            if (!$api = $this->getApiByOwnerName($ownerName)) {
                return false;
            }

            if (!$owner = $api->import($ownerName)) {
                return false;
            }

            if ($owner instanceof Owner) {
                $this->entityManager->persist($owner);
                if ($flushEntities) {
                    $this->entityManager->flush();
                }
            }
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
        try {
            $githubOwner = $this->github->api('user')->show($ownerName);

            // Data fetched, but not in expected format ?
            if (!isset($githubOwner['type'])) {
                return false;
            }
        } catch(RuntimeException $e) {
            // Api limit? User/organization not found? Don't continue
            return false;
        }

        if ($githubOwner['type'] != 'Organization') {
            $api = new GithubDeveloper($this->github, new NullOutput());
        } else {
            $api = new GithubOrganization($this->github, new NullOutput());
            $api->setOwnerManager($this);
        }

        return $api;
    }
}
