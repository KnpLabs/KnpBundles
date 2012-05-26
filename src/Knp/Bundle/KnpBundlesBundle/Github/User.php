<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;

use Github\Client;
use Github\HttpClient\Exception as GithubException;

use Knp\Bundle\KnpBundlesBundle\Entity;

class User
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \Github\Client|null
     */
    protected $github = null;

    /**
     * Output buffer
     *
     * @var OutputInterface|null
     */
    protected $output = null;

    /**
     * @param \Github\Client $github
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(Client $github, OutputInterface $output)
    {
        $this->github = $github;
        $this->output = $output;
    }

    public function import($name)
    {
        $user = new Entity\User();
        $user->setName($name);
        $user->setScore(0);
        if (!$this->update($user)) {
            return false;
        }
        return $user;
    }

    public function update(Entity\User $user)
    {
        try {
            $data = $this->github->getUserApi()->show($user->getName());
        } catch(GithubException $e) {
            if (404 === $e->getCode()) {
                // User has been removed
                return false;
            }
            return true;
        }

        $user->setEmail(isset($data['email']) ? $data['email'] : null);
        $user->setGravatarHash(isset($data['gravatar_id']) ? $data['gravatar_id'] : null);
        $user->setFullName(isset($data['name']) ? $data['name'] : null);
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
        if ($scheme === null) {
            return "http://".$url;
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
     * @param  OutputInterface
     * @return null
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get github
     *
     * @return \Github\Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param  \Github\Client
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }
}
