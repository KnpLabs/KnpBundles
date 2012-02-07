<?php

namespace Knp\Bundle\KnpBundlesBundle\Security\Core\User;

use Knp\Bundle\KnpBundlesBundle\Entity\User;
use Knp\Bundle\KnpBundlesBundle\Entity\UserManager;

use Symfony\Component\HttpKernel\KernelInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * A User provider which is using together with OAuth bundle to create new
 * DB users and get existing users
 */
class UserProvider implements UserProviderInterface
{
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function loadUserByUsername($username)
    {
        return $this->userManager->getOrCreate($username);
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