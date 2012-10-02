<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\HttpClient\ApiLimitExceedException;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface,
    HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedUserResponseInterface;

use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper,
    Knp\Bundle\KnpBundlesBundle\Security\OAuth\Response\SensioConnectUserResponse;

class Developer extends Owner
{
    /**
     * {@inheritDoc}
     */
    public function import($response, $update = true)
    {
        $developer = new EntityDeveloper();
        if (is_string($response)) {
            $developer->setName($response);
        } else {
            $developer->setName($response->getNickname());
            $developer->setFullName($response->getRealName());
        }

        if ($response instanceof SensioConnectUserResponse) {
            $developer->setName($response->getLinkedAccount('github') ?: $response->getNickname());
            $developer->setGithubId($response->getLinkedAccount('github'));
            $developer->setSensioId($response->getNickname());
        } else {
            $developer->setGithubId($response->getNickname());
        }

        if ($response instanceof AdvancedUserResponseInterface) {
            $developer->setEmail($response->getEmail());
            $developer->setAvatarUrl($response->getProfilePicture());
        }

        if ($update && !$this->update($developer)) {
            return false;
        }

        return $developer;
    }

    /**
     * @param EntityDeveloper $developer
     *
     * @return boolean
     */
    public function update(EntityDeveloper $developer)
    {
        $keywords = array(
            $developer->getName()
        );
        if (null !== $developer->getFullName()) {
            $keywords[] = $developer->getFullName();
        }
        if (null !== $developer->getEmail()) {
            $keywords[] = $developer->getEmail();
        }

        $api  = $this->github->api('user');
        $data = $api->show($developer->getName());

        if (empty($data)) {
            foreach ($keywords as $field) {
                try {
                    $data = $api->search($field);
                    if (isset($data['users']) && 0 < count($data['users'])) {
                        $data = $data['users'][0];
                        // Let's call API one more time to get clean user data
                        $data = $api->show($data['login']);
                    }
                } catch(ApiLimitExceedException $e) {
                    break;
                }

                // Did we found user in this iteration ?
                if (!empty($data) && !isset($data['message'])) {
                    break;
                }
            }
        }

        // Developer has been removed / not found ?
        if (empty($data) || isset($data['message'])) {
            return false;
        }

        $developer->setFullName(isset($data['fullname']) ? $data['fullname'] : null);
        $developer->setEmail(isset($data['email']) ? $data['email'] : null);
        $developer->setAvatarUrl(isset($data['avatar_url']) ? $data['avatar_url'] : null);
        $developer->setCompany(isset($data['company']) ? $data['company'] : null);
        $developer->setLocation(isset($data['location']) ? $data['location'] : null);
        $developer->setUrl(isset($data['blog']) ? $this->fixUrl($data['blog']) : null);

        return true;
    }
}
