<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Github\Client;

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
     * @param Client $github
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
     * Fixes url.
     * Adds http protocol by default, when no protocol is specified.
     *
     * @param string $url
     * @return string
     */
    public function fixUrl($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (null === $scheme) {
            return 'http://'.$url;
        }

        return $url;
    }
}
