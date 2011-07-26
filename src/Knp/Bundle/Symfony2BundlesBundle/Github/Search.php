<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Github;

use Knp\Bundle\Symfony2BundlesBundle\Entity\Repo;
use Knp\Bundle\Symfony2BundlesBundle\Entity\Bundle;
use Symfony\Component\Console\Output\OutputInterface;
use Goutte\Client;

/**
 * Searches a variety of sources (Github and Google) for Symfony2 bundles.
 *
 * @author Thibault Duplessis
 * @author Matthieu Bontemps
 */
class Search
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \Github_Client
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

    public function __construct(\Github_Client $github, Client $browser, OutputInterface $output)
    {
        $this->github = $github;
        $this->browser = $browser;
        $this->output = $output;
    }

    /**
     * Get a list of Symfony2 Repos from GitHub & Google
     *
     * @param integer $limit The maximum number of results to return
     */
    public function searchRepos($limit = 300)
    {
        $repos = array();
        $nb = 0;

        $repos = $this->searchReposOnTwitter('(#symfony2bundles OR #symfony2 OR #symfony) github filter:links', $repos, $limit);
        $this->output->writeln(sprintf('%d repos found!', count($repos) - $nb));
        $nb = count($repos);
        if ($nb >= $limit) {
            return array_slice($repos, 0, $limit);
        }

        $repos = $this->searchReposOnGitHub('Bundle', $repos, $limit, function($repo) { return $repo instanceof Bundle; });
        $repos = $this->searchReposOnGitHub('Symfony2', $repos, $limit);
        $this->output->writeln(sprintf('%d repos found!', count($repos) - $nb));
        $nb = count($repos);
        if ($nb >= $limit) {
            return array_slice($repos, 0, $limit);
        }

        $repos = $this->searchReposOnGoogle($repos, $limit);
        $this->output->writeln(sprintf('%d repos found!', count($repos) - $nb));
        $nb = count($repos);

        return array_slice($repos, 0, $limit);
    }

    protected function searchReposOnGitHub($query, array $repos, $limit, $filter = null)
    {
        $this->output->write(sprintf('Search "%s" on Github', $query));
        try {
            $page = 1;
            do {
                $found = $this->github->getRepoApi()->search($query, 'php', $page);
                if (empty($found)) {
                    break;
                }
                foreach ($found as $repo) {
                    $name = $repo['username'].'/'.$repo['name'];
                    $entity = Repo::create($name);
                    if (null === $filter || call_user_func($filter, $entity)) {
                        $repos[strtolower($name)] = $entity;
                    }
                }
                $page++;
                $this->output->write('...'.count($repos));
            } while (count($repos) < $limit);
        } catch (\Exception $e) {
            $this->output->write(' - '.$e->getMessage());
        }
        $this->output->writeln('... DONE');

        return $repos;
    }

    protected function searchReposOnGoogle(array $repos, $limit)
    {
        $this->output->write('Search on Google');
        $maxBatch = 5;
        $maxPage = 5;
        $pageNumber = 1;
        for ($batch = 1; $batch <= $maxBatch; $batch++) {
            for ($page = 1; $page <= $maxPage; $page++) {
                $url = sprintf('http://www.google.com/search?q=%s&start=%d',
                    urlencode('site:github.com Symfony2 Bundle'),
                    (1 === $pageNumber) ? '' : $pageNumber
                );
                $crawler = $this->browser->request('GET', $url);
                $links = $crawler->filter('#center_col ol li h3 a');
                if (0 != $links->count()) {
                    $this->output->write('.');
                } else {
                    $this->output->write(sprintf(' - No link - [%s]', $this->browser->getResponse()->getStatus()));
                    break 2;
                }
                foreach ($links->extract('href') as $url) {
                    if (!preg_match('#^http://github.com/([\w-]+/[\w-]+).*$#', $url, $match)) {
                        continue;
                    }
                    $name = $match[1];
                    if(!isset($repos[strtolower($name)])) {
                        $repo = Repo::create($name);
                        $repos[strtolower($name)] = $repo;
                        $this->output->write(sprintf('!'));
                    }
                }
                $pageNumber++;
                if (count($repos) >= $limit) {
                    // No need to keep searching and waiting.
                    break 2;
                }
                usleep(500 * 1000);
            }
            $this->output->write(sprintf('%d/%d', 10 * ($pageNumber - 1), $maxBatch * $maxPage * 10));
            sleep(2);
        }
        $this->output->writeln(' DONE');

        return $repos;
    }

    protected function searchReposOnTwitter($query, array $repos, $limit)
    {
        $this->output->write(sprintf('Search "%s" on Twitter', $query));

        $url = sprintf('http://search.twitter.com/search.json?q=%s&rpp=%d', urlencode($query), 100);
        $this->browser->request('GET', $url);
        $data = $this->browser->getResponse()->getContent();
        $data = json_decode($data, true);

        $alreadyFound = array();

        if ($data) {
            $results = $data['results'];
            foreach ($results as $result) {
                $tweet = $result['text'];

                // Search urls in the tweet
                if (preg_match_all("#https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?#i", $tweet, $m)) {
                    $urls = $m[0];
                    foreach ($urls as $url) {
                        $url = rtrim($url, '.');
                        if (isset($alreadyFound[$url])) {
                            continue;
                        }
                        $alreadyFound[$url] = true;
                        // The url is perhaps directly a github url
                        if (preg_match('#^https?://github.com/([^/]+/[^/]+)(/.*)?#', $url, $m)) {
                            $name = $m[1];
                            $repos[strtolower($name)] = Repo::create($name);

                        // Or a redirect/multi-redirect link => we parse the resulting github page
                        } else {
                            try {
                                $html = file_get_contents($url);
                            } catch (\ErrorException $e) {
                                continue;
                            }

                            if (preg_match('#<title>([a-z0-9-_]+/[^\'"/ ]+) - GitHub</title>#i', $html, $m)) {
                                $name = $m[1];
                                $repos[strtolower($name)] = Repo::create($name);
                            }
                        }
                    }
                }
            }
        }
        $this->output->writeln('... DONE');

        return $repos;
    }

    /**
     * Get browser
     *
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
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Get github
     *
     * @return \Github_Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param  \Github_Client
     * @return null
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }
}
