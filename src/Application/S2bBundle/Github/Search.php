<?php

namespace Application\S2bBundle\Github;
use Application\S2bBundle\Document\Bundle;
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
        $bundles = $this->searchBundlesOnGitHub($limit);
        $bundles = $this->searchBundlesOnGoogle($bundles, $limit);
        return $bundles;
    }

    protected function searchBundlesOnGitHub($limit)
    {
        $this->output->write('Search on Github');
        try {
            $bundles = array();
            $page = 1;
            do {
                $repos = $this->github->getRepoApi()->search('Bundle', 'php', $page);
                if(empty($repos)) {
                    break;
                }
                foreach($repos as $repo) {
                    if(!preg_match('#^\w+Bundle$#', $repo['name'])) {
                        continue;
                    }
                    $bundles[] = new Bundle($repo['username'].'/'.$repo['name']);
                }
                $page++;
                $this->output->write('...'.count($bundles));
            }
            while(count($bundles) < $limit);
        }
        catch(\Exception $e) {
            $this->output->write(' - '.$e->getMessage());
        }

        if(empty($bundles)) {
            $this->output->writeLn(' - Failed, will retry');
            sleep(3);
            return $this->searchBundlesOnGitHub($limit);
        }
        $this->output->writeLn('... DONE');
        return $bundles;
    }

    protected function searchBundlesOnGoogle(array $bundles, $limit)
    {
        $this->output->write('Search on Google');
        $maxBatch = 2;
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
                    if(!preg_match('#^http://github.com/(\w+/\w+Bundle).*$#', $url, $match)) {
                        continue;
                    }
                    $bundle = new Bundle($match[1]);
                    $alreadyFound = false;
                    foreach($bundles as $_bundle) {
                        if($bundle->getName() == $_bundle->getName()) {
                            $alreadyFound = true;
                            break;
                        }
                    }
                    if(!$alreadyFound) {
                        $bundles[] = $bundle;
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
        return $bundles;
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
