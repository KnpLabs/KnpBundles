<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

/**
 * Finds github repositories using Google
 *
 * @package KnpBundles
 */
class Google implements FinderInterface
{
    const ENDPOINT         = 'http://www.google.com/search';
    const PARAMETER_QUERY  = 'q';
    const PARAMETER_START  = 'start';
    const RESULTS_PER_PAGE = 10;

    private $query;
    private $limit;
    private $client;

    /**
     * Constructor
     *
     * @param string  $query
     * @param integer $limit
     * @param Client  $client
     */
    public function __construct($query = null, $limit = 300, Client $client = null)
    {
        $this->setQuery($query);
        $this->setLimit($limit);

        if (null === $client) {
            $client = new Client();
        }

        $this->client = $client;
    }

    /**
     * Defines the query
     *
     * @param  string $query
     */
    public function setQuery($query)
    {
        $this->query = strval($query);
    }

    /**
     * Defines the limit of results to fetch
     *
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        $this->limit = intval($limit);
    }

    /**
     * {@inheritDoc}
     */
    public function find()
    {
        if (empty($this->query)) {
            throw new \LogicException('You must specify a query to find repositories.');
        }

        $repositories = array();

        $page = 0;

        while (count($repositories) < $this->limit) {
            $page++;

            $results = $this->findPage($page);

            if (0 === count($results)) {
                break;
            }

            foreach ($results as $result) {
                if (in_array($result, $repositories)) {
                    $repositories[] = $result;
                }
            }
        }

        return array_slice($repositories, 0, $this->limit);
    }

    /**
     * Returns the URL to perform the search
     *
     * @param  integer $page The page number (default 1)
     *
     * @return string
     */
    private function buildUrl($page)
    {
        $params = array();
        $params[self::PARAMETER_QUERY] = $this->query;

        if ($page > 1) {
            $params[self::PARAMETER_START] = self::RESULTS_PER_PAGE * ($page - 1);
        }

        return self::ENDPOINT . '?' . http_build_query($params);
    }

    /**
     * Finds the repositories of the specified page url
     */
    private function findPage($page)
    {
        $repositories = array();
        $crawler = $this->client->request('GET', $this->buildUrl($page));
        $urls = $this->extractPageUrls($crawler);

        foreach ($urls as $url) {
            $repository = $this->extractUrlRepository($url);
            if (null !== $repository && !in_array($repository, $repositories)) {
                $repositories[] = $repository;
            }
        }

        return $repositories;
    }

    /**
     * Extracts the urls from the given google results crawler
     *
     * @param  Crawler $crawler
     *
     * @return array
     */
    private function extractPageUrls(Crawler $crawler)
    {
        return $crawler->filter('#center_col ol li h3 a')->extract('href');
    }

    /**
     * Returns the github repository extracted from the given URL
     *
     * @param  string $url
     *
     * @return string or NULL if the URL does not contain any repository
     */
    private function extractUrlRepository($url)
    {
        if (preg_match('/https?:\/\/(www.)?github.com\/(?<username>[\w_-]+)\/(?<repository>[\w_-]+)/', $url, $matches)) {
            return $matches['username'] . '/' . $matches['repository'];
        }

        return null;
    }
}
