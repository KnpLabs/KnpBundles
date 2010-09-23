<?php

namespace Application\S2bBundle\Github;
use Application\S2bBundle\Entity\Repo;
use Symfony\Component\Console\Output\OutputInterface;
use Goutte\Client;

/**
 * Searches a variety of sources (Github and Google) for Symfony2 bundles.
 *
 * @author Thibault Duplessis
 */
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
     * Get a list of Symfony2 Repos from GitHub & Google
     *
     * @integer limit $limit The maximum number of results to return
     */
    public function searchRepos($limit = 300)
    {
        $repos = array();
        $repos = $this->searchReposOnGitHub('Bundle', $repos, $limit);
        foreach($repos as $index => $repo) {
            if(!preg_match('/Bundle$/', $repo->getName())) {
                unset($repos[$index]);
            }
        }
        $repos = $this->searchReposOnGitHub('Symfony2', $repos, $limit);
        //$repos = $this->searchReposOnGoogle($repos, $limit);

        return array_slice($repos, 0, $limit);
    }

    protected function searchReposOnGitHub($query, array $repos, $limit)
    {
        $this->output->write(sprintf('Search "%s" on Github', $query));
        try {
            $page = 1;
            do {
                $found = $this->github->getRepoApi()->search($query, 'php', $page);
                if(empty($found)) {
                    break;
                }
                foreach($found as $repo) {
                    $repos[] = Repo::create($repo['username'].'/'.$repo['name']);
                }
                $page++;
                $this->output->write('...'.count($repos));
            }
            while(count($repos) < $limit);
        }
        catch(\Exception $e) {
            $this->output->write(' - '.$e->getMessage());
        }
        $this->output->writeLn('... DONE');

        return array_slice($repos, 0, $limit);
    }

    protected function searchReposOnGoogle(array $repos, $limit)
    {
        $this->output->write('Search on Google');
        $maxBatch = 5;
        $maxPage = 5;
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
                    $this->output->write(sprintf(' - No link - [%s]', $this->browser->getResponse()->getStatus()));
                    break 2;
                }
                foreach($links->extract('href') as $url) {
                    if(!preg_match('#^http://github.com/([\w-]+/[\w-]+).*$#', $url, $match)) {
                        continue;
                    }
                    $repo = Repo::create($match[1]);
                    $alreadyFound = false;
                    foreach($repos as $_repo) {
                        if($repo->getName() == $_repo->getName()) {
                            $alreadyFound = true;
                            break;
                        }
                    }
                    if(!$alreadyFound) {
                        $repos[] = $repo;
                        $this->output->write(sprintf('!'));
                    }
                }
                $pageNumber++;
                usleep(500*1000);
            }
            $this->output->write(sprintf('%d/%d', 10*($pageNumber - 1), $maxBatch*$maxPage*10));
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
