<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

/**
 * Finds github repositories using the github api
 *
 * @package KnpBundles
 */
class Github extends CommonFinder
{
    const ENDPOINT         = 'https://github.com/search';
    const PARAMETER_QUERY  = 'q';
    const PARAMETER_START  = 'start_value';
    const RESULTS_PER_PAGE = 30;

    /**
     * {@inheritdoc}
     */
    protected function buildUrl($page)
    {
        $params = array(
            self::PARAMETER_QUERY => $this->query,
            'repo'                => null,
            'langOverride'        => null,
            'type'                => 'Repositories',
            'language'            => 'PHP',
        );

        if ($page > 1) {
            $params[self::PARAMETER_START] = self::RESULTS_PER_PAGE * ($page - 1);
        }

        return self::ENDPOINT . '?' . http_build_query($params);
    }

    /**
     * Extracts the urls from the given google results crawler
     *
     * @param  Crawler $crawler
     *
     * @return array
     */
    protected function extractPageUrls(Crawler $crawler)
    {
        return $crawler->filter('#code_search_results .result h2 a')->extract('href');
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
        if (preg_match('/\/(?<username>[\w_-]+)\/(?<repository>[\w_-]+)/', $url, $matches)) {
            return $matches['username'] . '/' . $matches['repository'];
        }

        return null;
    }
}
