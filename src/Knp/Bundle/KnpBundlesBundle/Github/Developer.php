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
     * @param string|UserResponseInterface $response
     *
     * @return boolean|EntityDeveloper
     */
    public function import($response)
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
        }

        if ($response instanceof AdvancedUserResponseInterface) {
            $developer->setEmail($response->getEmail());
            $developer->setAvatarUrl($response->getProfilePicture());
        }

        if (!$this->update($developer)) {
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
