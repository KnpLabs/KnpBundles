<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Console\Output\NullOutput;

use Github\Client;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;
use Knp\Bundle\KnpBundlesBundle\Github\User as GithubUser;
use Knp\Bundle\KnpBundlesBundle\Security\OAuth\Response\SensioConnectUserResponse;

/**
 * Manages user entities
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class UserManager
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    private $entityManager;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * @var GithubUser
     */
    private $githubUserApi;

    /**
     * @param ObjectManager $entityManager
     */
    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\User');
        $this->githubUserApi = new GithubUser(new Client(), new NullOutput());
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function findUserBy(array $data)
    {
        return $this->repository->findOneBy($data);
    }

    /**
     * @param string|UserResponseInterface $data
     *
     * @return User
     *
     * @throws UserNotFoundException
     */
    public function getOrCreate($data)
    {
        $username = $data;
        if ($data instanceof UserResponseInterface) {
            if ($data instanceof SensioConnectUserResponse) {
                $username = $data->getLinkedAccount('github') ?: $data->getUsername();
            } else {
                $username = $data->getUsername();
            }
        }

        if (!$user = $this->findUserBy(array('name' => $username))) {
            if (!$user = $this->githubUserApi->import($data)) {
                throw new UserNotFoundException();
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
