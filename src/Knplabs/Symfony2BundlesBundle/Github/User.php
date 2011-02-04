<?php

namespace Knplabs\Symfony2BundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Knplabs\Symfony2BundlesBundle\Entity;

class User
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \phpGitHubApi
     */
    protected $github = null;

    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    protected $output = null;

    public function __construct(\phpGitHubApi $github, OutputInterface $output)
    {
        $this->github = $github;
        $this->output = $output;
    }

    public function import($name)
    {
        $user = new Entity\User();
        $user->setName($name);
        if(!$this->update($user)) {
            return false;
        }
        return $user;
    }

    public function update(Entity\User $user)
    {
        $data = $this->github->getUserApi()->show($user->getName());

        $user->setEmail(isset($data['email']) ? $data['email'] : null);
        $user->setFullName(isset($data['name']) ? $data['name'] : null);
        $user->setCompany(isset($data['company']) ? $data['company'] : null);
        $user->setLocation(isset($data['location']) ? $data['location'] : null);
        $user->setBlog(isset($data['blog']) ? $data['blog'] : null);

        return true;
    }

    /**
     * Get output
     * @return OutputInterface
     */
    public function getOutput()
    {
      return $this->output;
    }

    /**
     * Set output
     * @param  OutputInterface
     * @return null
     */
    public function setOutput($output)
    {
      $this->output = $output;
    }

    /**
     * Get github
     * @return \phpGitHubApi
     */
    public function getGitHubApi()
    {
        return $this->github;
    }

    /**
     * Set github
     * @param  \phpGitHubApi
     * @return null
     */
    public function setGitHubApi($github)
    {
        $this->github = $github;
    }

}
