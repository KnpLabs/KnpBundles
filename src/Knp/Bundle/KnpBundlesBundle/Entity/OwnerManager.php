<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\NullOutput;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

use Github\Client;

use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException,
    Knp\Bundle\KnpBundlesBundle\Security\OAuth\Response\SensioConnectUserResponse;

use Knp\Bundle\KnpBundlesBundle\Github\Developer as GithubDeveloper,
    Knp\Bundle\KnpBundlesBundle\Github\Organization as GithubOrganization,
    Knp\Bundle\KnpBundlesBundle\Github\OwnerInterface as GithubOwnerInterface;

/**
 * Manages user entities
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class OwnerManager
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Repository\OwnerRepository
     */
    private $repository;

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
        $this->repository    = $entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Owner');
        $this->github        = $github;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function findOwnerBy(array $data)
    {
        return $this->repository->findOneBy($data);
    }

    /**
     * @param string|UserResponseInterface $data
     *
     * @return Developer
     *
     * @throws UserNotFoundException
     */
    public function getOrCreate($data)
    {
        if (is_string($data)) {
            $ownerName = $data;
        } elseif ($data instanceof UserResponseInterface) {
            if ($data instanceof SensioConnectUserResponse) {
                $ownerName = $data->getLinkedAccount('github') ?: $data->getNickname();
            } else {
                $ownerName = $data->getNickname();
            }
        }

        if (!$owner = $this->findOwnerBy(array('name' => $ownerName))) {
            if (!$api = $this->getApiByOwnerName($ownerName)) {
                return false;
            }

            $owner = $api->import($data);

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
            $api->setRepository($this->repository);
        }

        return $api;
    }
}
