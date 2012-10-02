<?php

namespace Knp\Bundle\KnpBundlesBundle\Security\Core\User;

use Knp\Bundle\KnpBundlesBundle\Entity\OwnerManager;

use Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface,
    HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

/**
 * A User provider which is using together with OAuth bundle to create new
 * DB users and get existing users
 */
class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var OwnerManager
     */
    protected $ownerManager;

    /**
     * @param OwnerManager $userManager
     */
    public function __construct(OwnerManager $userManager)
    {
        $this->ownerManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->ownerManager->getOrCreate($response, 'developer');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->ownerManager->getOrCreate($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $refreshedUser = $this->ownerManager->findDeveloperBy(array('id' => $user->getId()));
        if (null === $refreshedUser) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
        }

        return $refreshedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Knp\Bundle\KnpBundlesBundle\Entity\User';
    }
}
