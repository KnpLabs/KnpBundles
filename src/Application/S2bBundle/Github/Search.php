<?php

namespace Application\S2bBundle\Github;
use Symfony\Components\Console\Output\OutputInterface;
use Goutte\Client;

class Search
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \phpGitHubApi
     */
    protected $github = null;

    /**
     * Web browser
     *
     * @var Client
     */
    protected $browser = null;

    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    protected $output = null;
    
    public function __construct(\phpGitHubApi $github, Client $browser, OutputInterface $output)
    {
        $this->github = $github;
        $this->browser = $browser;
        $this->output = $output;
    }
    
    /**
     * Get a list of Symfony2 Bundles from GitHub
     */
    public function searchBundles($limit = 300)
    {
        $repos = $this->searchBundlesOnGitHub($limit);
        //$repos = $this->searchBundlesOnGoogle($repos, $limit);
        return $repos;
    }

    protected function searchBundlesOnGitHub($limit)
    {
        $this->output->write('Search on Github');
        try {
            $repos = array();
            $page = 1;
            do {
                $pageRepos = $this->github->getRepoApi()->search('Bundle', 'php', $page);
                if(empty($pageRepos)) {
                    break;
                }
                foreach($pageRepos as $pageRepo) {
                    if(!preg_match('#^\w+Bundle$#', $pageRepo['name'])) {
                        continue;
                    }
                    $repos[] = $pageRepo;
                }
                $page++;
                $this->output->write('...'.count($repos));
            }
            while(count($repos) < $limit);
        }
        catch(\Exception $e) {
            $this->output->writeLn(' ~ '.$e->getMessage());
        }

        if(empty($repos)) {
            $this->output->writeLn(' - Failed, will retry');
            sleep(3);
            return $this->searchBundles($limit);
        }
        $this->output->writeLn('... DONE');
        return $repos;
    }

    protected function searchBundlesOnGoogle(array $repos, $limit)
    {
        $this->output->write('Search on Google');
        $maxBatch = 5;
        $maxPage = 6;
        $pageNumber = 1;
        for($batch = 1; $batch <= $maxBatch; $batch++) {
            for($page = 1; $page <= $maxPage; $page++) {
                $url = sprintf('http://www.google.com/search?q=%s&start=%d',
                    urlencode('site:github.com Symfony2 Bundle'),
                    (1 === $pageNumber) ? '' : $pageNumber
                );
                $crawler = $this->browser->request('GET', $url);
                $links = $crawler->filter('#center_col ol li h3 a');
                if(0 != $links->count()) {
                    $this->output->write('.');
                }
                else {
                    $this->output->write(sprintf(' [%s]', $this->browser->getResponse()->getStatus()));
                    break 2;
                }
                foreach($links->extract('href') as $url) {
                    if(!preg_match('#^http://github.com/(\w+)/(\w+Bundle).*$#', $url, $match)) {
                        continue;
                    }
                    if(!file_exists(sprintf('http://github.com/%s/%s', $username, $name))) {
                        continue;
                    }
                    $username = $match[1];
                    $name = $match[2];
                    $exists = false;
                    foreach($repos as $repo) {
                        if($repo['name'] == $name && $repo['username'] == $username) {
                            $exists = true;
                            break;
                        }
                    }
                    if(!$exists) {
                        try {
                            $repo = $this->github->getRepoApi()->show($username, $name);
                            $existsOnGithub = true;
                        }
                        catch(\phpGitHubApiRequestException $e) {
                            $existsOnGithub = false;
                        }
                        if($existsOnGithub) {
                            $repo['username'] = $repo['owner'];
                            $repos[] = $repo;
                            $this->output->write('!');
                        }
                    }
                }
                $pageNumber++;
                usleep(500*1000);
            }
            $this->output->write(sprintf('%d/%d', 10*$pageNumber - 1, $maxBatch*$maxPage*10));
            sleep(2);
        }
        $this->output->writeLn(' DONE');
        return $repos;
    }

    /**
     * Get browser
     * @return Client
     */
    public function getBrowser()
    {
      return $this->browser;
    }
    
    /**
     * Set browser
     * @param  Client
     * @return null
     */
    public function setBrowser($browser)
    {
      $this->browser = $browser;
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
