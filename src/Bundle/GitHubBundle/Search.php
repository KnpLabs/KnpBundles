<?php

namespace Bundle\GitHubBundle;

class Search
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \phpGitHubApi
     */
    protected $github = null;
    
    public function __construct()
    {
        $this->github = new \phpGitHubApi();
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

    /**
     * Get a list of Symfony2 Bundles from GitHub
     */
    public function searchBundles()
    {
        $repos = $this->github->getRepoApi()->search('Symfony2+Bundle');

        return $repos;
    }
}
