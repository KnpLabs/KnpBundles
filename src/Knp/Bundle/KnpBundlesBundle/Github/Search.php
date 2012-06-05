<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
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

    /**
     * @param \Goutte\Client $browser
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(Client $browser, OutputInterface $output)
    {
        $this->browser = $browser;
        $this->output  = $output;
    }

    /**
     * Get a list of Symfony2 bundles from GitHub & Google
     *
     * @param integer $limit The maximum number of results to return
     *
     * @return array
     */
    public function searchBundles()
    {
        $repos = array();
        $nb = 0;

        $limit = 2000;

        $repos = $this->searchBundlesOnTwitter('(#knpbundles OR #symfony2 OR #symfony) github filter:links', $repos, $limit);
        $this->output->writeln(sprintf('%d repos found!', count($repos) - $nb));
        $nb = count($repos);
        if ($nb >= $limit) {
            return array_slice($repos, 0, $limit);
        }

        $repos = $this->searchBundlesOnGitHub('"Bundle"', $repos, $limit);
        $repos = $this->searchBundlesOnGitHub('"Symfony2"', $repos, $limit);
        $this->output->writeln(sprintf('%d repos found!', count($repos) - $nb));
        $nb = count($repos);
        if ($nb >= $limit) {
            return array_slice($repos, 0, $limit);
        }

        $repos = $this->searchBundlesOnGoogle($repos, $limit);
        $this->output->writeln(sprintf('%d repos found!', count($repos) - $nb));

        return array_slice($repos, 0, $limit);
    }

    protected function searchBundlesOnGitHub($query, array $repos, $limit)
    {
        $this->output->write(sprintf('Search "%s" on Github', $query));

        $maxBatch = 5;
        $maxPage  = 5;
        $pageNumber = 1;
        for ($batch = 1; $batch <= $maxBatch; $batch++) {
            for ($page = 1; $page <= $maxPage; $page++) {
                $url = sprintf('https://github.com/search?q=%s&repo=&langOverride=&start_value=%d&type=Repositories&language=PHP',
                    urlencode($query),
                    $pageNumber
                );
                $crawler = $this->browser->request('GET', $url);

                $maxPage = $crawler->filter('.results_and_sidebar .results .pagination a')->last()->text();

                $links = $crawler->filter('.results_and_sidebar .results .result h2 a');
                if (0 === $links->count()) {
                    $this->output->write(sprintf(' - No link - [%s]', $this->browser->getResponse()->getStatus()));
                    break 2;
                }
                $this->output->write('.');

                foreach ($links->extract('href') as $url) {
                    if (!preg_match('#^/([\w-]+/[\w-]+).*$#', $url, $match)) {
                        continue;
                    }
                    $name = $match[1];
                    if (!isset($repos[strtolower($name)])) {
                        if (!$this->isValidBundleName($name)) {
                            continue;
                        }
                        $repo = new Bundle($name);
                        $repos[strtolower($name)] = $repo;
                        $this->output->write(sprintf('!'));
                    }
                }
                ++$pageNumber;
                if (count($repos) >= $limit) {
                    // No need to keep searching and waiting.
                    break 2;
                }
                usleep(500 * 1000);
            }
            $this->output->write(sprintf('%d/%d', 30 * ($pageNumber - 1), $maxBatch * $maxPage * 30));
            sleep(2);
        }
        $this->output->writeln('... DONE');

        return $repos;
    }

    protected function searchBundlesOnGoogle(array $repos, $limit)
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
                if (0 === $links->count()) {
                    $this->output->write(sprintf(' - No link - [%s]', $this->browser->getResponse()->getStatus()));
                    break 2;
                }
                $this->output->write('.');

                foreach ($links->extract('href') as $url) {
                    if (!preg_match('#^http://github.com/([\w-]+/[\w-]+).*$#', $url, $match)) {
                        continue;
                    }
                    $name = $match[1];
                    if (!isset($repos[strtolower($name)])) {
                        if (!$this->isValidBundleName($name)) {
                            continue;
                        }
                        $repo = new Bundle($name);
                        $repos[strtolower($name)] = $repo;
                        $this->output->write(sprintf('!'));
                    }
                }
                ++$pageNumber;
                if (count($repos) >= $limit) {
                    // No need to keep searching and waiting.
                    break 2;
                }
                usleep(500 * 1000);
            }
            $this->output->write(sprintf('%d/%d', 10 * ($pageNumber - 1), $maxBatch * $maxPage * 10));
            sleep(2);
        }
        $this->output->writeln('... DONE');

        return $repos;
    }

    protected function searchBundlesOnTwitter($query, array $repos, $limit)
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
                            if (!$this->isValidBundleName($name)) {
                                continue;
                            }
                            $repos[strtolower($name)] = new Bundle($name);

                        // Or a redirect/multi-redirect link => we parse the resulting github page
                        } else {
                            try {
                                $html = file_get_contents($url);
                            } catch (\ErrorException $e) {
                                continue;
                            }

                            if (preg_match('#<title>([a-z0-9-_]+/[^\'"/ ]+) - GitHub</title>#i', $html, $m)) {
                                $name = $m[1];
                                if (!$this->isValidBundleName($name)) {
                                    continue;
                                }
                                $repos[strtolower($name)] = new Bundle($name);
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
     *
     * @param  Client
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
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Check if a bundle name is really a bundle name
     *
     * @param string $name name of the bundle
     *
     * @return bool
     */
    protected function isValidBundleName($name)
    {
        return (bool) preg_match('@Bundle$@', $name);
    }
}
