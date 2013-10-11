<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Github\Api\Repo;
use Symfony\Component\DomCrawler\Crawler;
use Github\Client;

/**
 * Finds github repositories using the github api
 */
class Github extends AbstractBaseFinder
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @var Client
     */
    private $github;

    /**
     * @param null $query
     * @param int $limit
     * @param Client $github
     */
    public function __construct(Client $github, $query = null, $limit = 300)
    {
        parent::__construct($query, $limit);

        $this->github = $github;
    }

    /**
     * Finds the repositories
     *
     * @return array
     */
    public function find()
    {
        /** @var Repo $repositoryApi */
        $repositoryApi = $this->github->api('repo');

        $repositories = array();
        $page         = 1;

        // Doesn't fetch more than 1000 results because github doesn't authorize this trick
        // Notice that the crawling as an identical result
        do {
            $repositoriesData = $repositoryApi->find($this->query, array('language' => 'php', 'per-page' => 100, 'start_page' => $page));
            $repositoriesData = $repositoriesData['repositories'];

            foreach ($repositoriesData as $repositoryData) {
                $repositories[] = $this->extractUrlRepository($repositoryData['url']);
            }
            $page++;

        } while (!empty($repositoriesData) && $page < 10);

        return $repositories;
    }

    /**
     * Returns the github repository extracted from the given URL
     *
     * @param  string $url
     *
     * @return string or NULL if the URL does not contain any repository
     */
    protected function extractUrlRepository($url)
    {
        if (preg_match('/https:\/\/github\.com\/(?<username>[\w\.-]+)\/(?<repository>[\w\.-]+)/', $url, $matches)) {
            return $matches['username'] . '/' . $matches['repository'];
        }

        return null;
    }
}
