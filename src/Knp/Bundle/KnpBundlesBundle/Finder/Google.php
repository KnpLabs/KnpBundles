<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

/**
 * Finds github repositories using Google
 */
class Google extends CommonFinder
{
    const ENDPOINT         = 'http://www.google.com/search';
    const PARAMETER_QUERY  = 'q';
    const PARAMETER_START  = 'start';
    const RESULTS_PER_PAGE = 10;

    /**
     * {@inheritdoc}
     */
    protected function buildUrl($page)
    {
        $params = array();
        $params[self::PARAMETER_QUERY] = $this->query;

        if ($page > 1) {
            $params[self::PARAMETER_START] = self::RESULTS_PER_PAGE * ($page - 1);
        }

        return self::ENDPOINT . '?' . http_build_query($params);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractPageUrls(Crawler $crawler)
    {
        return $crawler->filter('#center_col ol li h3 a')->extract('href');
    }

    /**
     * {@inheritdoc}
     */
    protected function extractUrlRepository($url)
    {
        if (preg_match('/https?:\/\/(www.)?github.com\/(?<username>[\w_-]+)\/(?<repository>[\w_-]+)/', $url, $matches)) {
            return $matches['username'] . '/' . $matches['repository'];
        }

        return null;
    }
}
