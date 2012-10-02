<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

use Github\Client;

use Knp\Bundle\KnpBundlesBundle\Security\OAuth\Response\SensioConnectUserResponse;
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
     * @param string|UserResponseInterface $data
     * @param string                       $entityType
     *
     * @return Owner
     *
     * @throws UsernameNotFoundException
     */
    public function getOrCreate($data, $entityType = 'owner')
    {
        if (is_string($data)) {
            $findBy = array('name' => $data);
        } elseif ($data instanceof UserResponseInterface) {
            if ($data instanceof SensioConnectUserResponse) {
                if ($data->getLinkedAccount('github')) {
                    $findBy = array('githubId' => $data->getLinkedAccount('github'));
                } else {
                    $findBy = array('sensioId' => $data->getNickname());
                }
            } else {
                $findBy = array('githubId' => $data->getNickname());
            }
        }

        if ('developer' == $entityType) {
            $owner = $this->findDeveloperBy($findBy);
        } else {
            $owner = $this->findOwnerBy($findBy);
        }

        if (!$owner) {
            if (is_string($data)) {
                $this->throwUserNotFoundException($data);
            }

            // SensioLabs response is always an developer connection
            if ($data instanceof SensioConnectUserResponse) {
                $api = new GithubDeveloper($this->github, new NullOutput());
            } elseif (!$api = $this->getApiByOwnerName(current($findBy))) {
                $this->throwUserNotFoundException($data->getNickname());
            }

            if (!$owner = $api->import($data, false)) {
                $this->throwUserNotFoundException($data->getNickname());
            }

            $this->entityManager->persist($owner);
            $this->entityManager->flush();
        }

        return $owner;
    }

    /**
     * @param string $ownerName
     *
     * @return GithubOwnerInterface
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

    /**
     * @param string $username
     *
     * @throws UsernameNotFoundException
     */
    private function throwUserNotFoundException($username)
    {
        throw new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
    }
}
