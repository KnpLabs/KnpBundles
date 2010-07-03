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
    public function searchBundles($limit = 300)
    {
        $repos = array();
        $page = 1;
        do {
            $pageRepos = $this->github->getRepoApi()->search('Bundle', 'php', $page);
            if(empty($pageRepos)) {
                break;
            }
            $repos = array_merge($repos, $pageRepos);
            $page++;
        }
        while(count($repos) < $limit);

        return $repos;
    }
}
