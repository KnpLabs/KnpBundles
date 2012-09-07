<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Symfony\Component\DomCrawler\Crawler;
use Buzz\Browser;

/**
 * Abstract class for finder
 *
 * @package KnpBundles
 */
abstract class CommonFinder implements FinderInterface
{
    /**
     * @var string
     */
    protected $query;
    /**
     * @var integer
     */
    protected $limit;
    /**
     * @var Browser
     */
    protected $browser;

    /**
     * Constructor
     *
     * @param string  $query
     * @param integer $limit
     */
    public function __construct($query = null, $limit = 300)
    {
        $this->setQuery($query);
        $this->setLimit($limit);
    }

    /**
     * @param Browser $browser
     */
    public function setBrowser(Browser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Defines the query
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Defines the limit of results to fetch
     *
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * {@inheritDoc}
     */
    public function find()
    {
        $repositories = array();

        $page = $counter = 0;

        do {
            ++$page;

            $results = $this->findPage($page);
            if (0 === count($results)) {
                break;
            }
            foreach ($results as $result) {
                if (!in_array($result, $repositories)) {
                    ++$counter;
                    $repositories[] = $result;
                }
            }
        } while ($counter < $this->limit);

        return array_slice($repositories, 0, $this->limit);
    }

    /**
     * Finds the repositories of the specified page url
     *
     * @param integer $page
     *
     * @return array
     */
    protected function findPage($page)
    {
        $crawler = $this->doRequest($page);

        $urls = $this->extractPageUrls($crawler);

        $repositories = array();
        foreach ($urls as $url) {
            $repository = $this->extractUrlRepository($url);
            if (null !== $repository && !in_array($repository, $repositories)) {
                $repositories[] = $repository;
            }
        }

        return $repositories;
    }

    /**
     * Returns the URL to perform the search
     *
     * @param  integer $page The page number (default 1)
     *
     * @return string
     */
    abstract protected function buildUrl($page);

    /**
     * Extracts the urls from the given google results crawler
     *
     * @param  Crawler $crawler
     *
     * @return array
     */
    abstract protected function extractPageUrls(Crawler $crawler);

    /**
     * Returns the github repository extracted from the given URL
     *
     * @param  string $url
     *
     * @return string or NULL if the URL does not contain any repository
     */
    abstract protected function extractUrlRepository($url);

    /**
     * @param integer $page
     *
     * @return Crawler
     */
    private function doRequest($page)
    {
        $response = $this->browser->get($this->buildUrl($page));

        $crawler = new Crawler();
        $crawler->add($response->toDomDocument());

        return $crawler;
    }
}
