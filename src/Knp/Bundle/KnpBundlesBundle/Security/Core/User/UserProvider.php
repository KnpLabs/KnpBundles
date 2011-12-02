<?php

namespace Knp\Bundle\KnpBundlesBundle\Security\Core\User;

use Knp\Bundle\KnpBundlesBundle\Entity\User;

use Symfony\Component\HttpKernel\KernelInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * A User provider which is using together with OAuth bundle to create new
 * DB users and get existing users
 */
class UserProvider implements UserProviderInterface
{
    protected $em;
    protected $container;

    public function __construct($em, $updater)
    {
        $this->em = $em;
        $this->updater = $updater;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository('KnpBundlesBundle:User')->findOneByName($username);

        if (!$user) {
            $user = new User();
            $user->setName($username);

            // Get GitHub user
            $user = $this->updater->getOrCreateUser($username);

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $user = $this->loadUserByUsername($user->getUsername());

        return $user;
    }

    public function supportsClass($class)
    {
        return $class === 'Knp\Bundle\KnpBundlesBundle\Entity\User';
    }
}