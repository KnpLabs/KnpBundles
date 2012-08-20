<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;

use Github\Client,
    Github\HttpClient\Exception as GithubException;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface,
    HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedUserResponseInterface;

use Knp\Bundle\KnpBundlesBundle\Entity\User as EntityUser,
    Knp\Bundle\KnpBundlesBundle\Security\OAuth\Response\SensioConnectUserResponse;

class User
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var Client
     */
    protected $github;

    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param Client $github
     * @param OutputInterface $output
     */
    public function __construct(Client $github, OutputInterface $output)
    {
        $this->github = $github;
        $this->output = $output;
    }

    /**
     * @param string|UserResponseInterface $response
     *
     * @return boolean|EntityUser
     */
    public function import($response)
    {
        $user = new EntityUser();
        if (is_string($response)) {
            $user->setName($response);
        } else {
            $user->setName($response->getUsername());
            $user->setFullName($response->getDisplayName());
        }

        if ($response instanceof SensioConnectUserResponse) {
            $user->setName($response->getLinkedAccount('github') ?: $response->getUsername());
        }

        if ($response instanceof AdvancedUserResponseInterface) {
            $user->setEmail($response->getEmail());
            $user->setGravatarHash($response->getProfilePicture());
        }

        if (!$this->update($user)) {
            return false;
        }

        return $user;
    }

    /**
     * @param EntityUser $user
     *
     * @return boolean
     */
    public function update(EntityUser $user)
    {
        $keywords = array(
            $user->getName()
        );
        if (null !== $user->getFullName()) {
            $keywords[] = $user->getFullName();
        }
        if (null !== $user->getEmail()) {
            $keywords[] = $user->getEmail();
        }

        $api  = $this->github->api('user');
        $data = null;
        try {
            $data = $api->show($user->getName());
        } catch(GithubException $e) {
        }

        if (empty($data)) {
            foreach ($keywords as $field) {
                try {
                    $data = $api->search($field);
                    if (isset($data['users']) && 0 < count($data['users'])) {
                        $data = $data['users'][0];
                        // Let's call API one more time to get clean user data
                        $data = $api->show($data['login']);
                    }
                } catch(GithubException $e) {
                    if (404 === $e->getCode()) {
                        continue;
                    }
                    break;
                }

                // Did we found user in this iteration ?
                if (!empty($data)) {
                    break;
                }
            }
        }

        // User has been removed / not found ?
        if (empty($data)) {
            return false;
        }

        $user->setFullName(isset($data['fullname']) ? $data['fullname'] : null);
        $user->setEmail(isset($data['email']) ? $data['email'] : null);
        $user->setGravatarHash(isset($data['avatar_url']) ? $data['avatar_url'] : (isset($data['gravatar_id']) ? $data['gravatar_id'] : null));
        $user->setCompany(isset($data['company']) ? $data['company'] : null);
        $user->setLocation(isset($data['location']) ? $data['location'] : null);
        $user->setBlog(isset($data['blog']) ? $this->fixUrl($data['blog']) : null);

        return true;
    }

    /**
     * Fixes url.
     * Adds http protocol by default, when no protocol is specified.
     *
     * @param string $url
     * @return string
     */
    protected function fixUrl($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (null === $scheme) {
            return 'http://'.$url;
        }

        return $url;
    }

    /**
     * Get output
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set output
     *
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get github
     *
     * @return Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param Client $github
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }
}
