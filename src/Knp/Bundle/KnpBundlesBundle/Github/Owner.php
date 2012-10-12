<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Github\Client;

use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper;
use Knp\Bundle\KnpBundlesBundle\Entity\Owner as EntityOwner;

abstract class Owner implements OwnerInterface
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
     * @param Client          $github
     * @param OutputInterface $output
     */
    public function __construct(Client $github, OutputInterface $output)
    {
        $this->github = $github;
        $this->output = $output;
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

    /**
     * @param EntityOwner $owner
     * @param array       $data
     */
    protected function updateOwner(EntityOwner $owner, array $data)
    {
        $owner->setFullName(isset($data['fullname']) ? $data['fullname'] : null);
        $owner->setEmail(isset($data['email']) ? $data['email'] : null);
        $owner->setAvatarUrl(isset($data['avatar_url']) ? $data['avatar_url'] : null);
        $owner->setLocation(isset($data['location']) ? $data['location'] : null);
        $owner->setUrl(isset($data['blog']) ? $this->fixUrl($data['blog']) : null);

        if ($owner instanceof EntityDeveloper) {
            $owner->setGithubId(isset($data['login']) ? $data['login'] : null);
            $owner->setCompany(isset($data['company']) ? $data['company'] : null);
        }
    }

    /**
     * Fixes url.
     * Adds http protocol by default, when no protocol is specified.
     *
     * @param string $url
     * @return string
     */
    private function fixUrl($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (null === $scheme) {
            return 'http://'.$url;
        }

        return $url;
    }
}
