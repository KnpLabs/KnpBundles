<?php

namespace Application\S2bBundle\GitHub;
use Symfony\Components\Console\Output\OutputInterface;

class Search
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \phpGitHubApi
     */
    protected $github = null;
    
    public function __construct(\phpGitHubApi $github)
    {
        $this->github = $github;
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
    public function searchBundles($limit = 300, OutputInterface $output = null)
    {
        try {
            $repos = array();
            $page = 1;
            do {
                $pageRepos = $this->github->getRepoApi()->search('Bundle', 'php', $page);
                if(empty($pageRepos)) {
                    break;
                }
                $repos = array_merge($repos, $pageRepos);
                $page++;
                if($output) $output->write('...'.count($repos));
            }
            while(count($repos) < $limit);
        }
        catch(Exception $e) {
            if($output) $output->writeLn($e->getMessage());
        }

        if(empty($repos)) {
            if($output) $output->writeLn('Failed, will retry');
            sleep(2);
            return $this->searchBundles($limit, $output);
        }

        if($output) $output->writeLn('... DONE');
        return $repos;
    }
}
