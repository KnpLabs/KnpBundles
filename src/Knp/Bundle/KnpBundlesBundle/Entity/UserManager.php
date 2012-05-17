<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Console\Output\NullOutput;

use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;
use Knp\Bundle\KnpBundlesBundle\Github;

/**
 * Manages user entities
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class UserManager
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * @var Knp\Bundle\KnpBundlesBundle\Github\User
     */
    private $githubUserApi;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\User');
        $this->githubUserApi = new Github\User(new \Github\Client(), new NullOutput());
    }

    public function getOrCreate($username)
    {
        if (!$user = $this->repository->findOneBy(array('name' => $username))) {
            if (!$user = $this->githubUserApi->import($username)) {
                throw new UserNotFoundException();
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
