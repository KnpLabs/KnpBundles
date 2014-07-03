<?php

namespace Knp\Bundle\KnpBundlesBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

use Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager;

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
        $findBy = array('name' => $response->getNickname());
        $findBy['githubId'] = $response->getNickname();

        $user = $this->ownerManager->findDeveloperBy($findBy);
        if (!$user) {
            $user = $this->ownerManager->createOwner($findBy['name']);
            if (!$user) {
                throw new UsernameNotFoundException(sprintf(
                    'User with username "%s" could not found or created. If your account doesn\'t exists as github account, we can\'t connect you for now.',
                    $findBy['name']
                ));
            }
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->ownerManager->findDeveloperBy(array('name' => $username));
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
        return $class === 'Knp\\Bundle\\KnpBundlesBundle\\Entity\\Developer';
    }
}
