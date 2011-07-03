<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Finder;

use Github_Client;

/**
 * Finds github repositories using the github api
 *
 * @package Symfony2Bundles
 */
class Github implements FinderInterface
{
    private $query;
    private $limit;
    private $client;

    /**
     * Constructor
     *
     * @param string        $query
     * @param integer       $limit
     * @param Github_Client $client
     */
    public function __construct($query, $limit = 300, Github_Client $client = null)
    {
        $this->setQuery($query);
        $this->setLimit($limit);

        if (null === $client) {
            $client = new Github_Client();
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
     * Defines the limit
     *
     * @param  integer $limit
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

        $api          = $this->client->getRepoApi();
        $page         = 0;
        $repositories = array();

        while (count($repositories) < $this->limit) {
            $page++;

            $results = $api->search($this->query, 'php', $page);

            if (0 === count($results)) {
                // break as soon as there is no result in the current page
                break;
            }

            foreach ($results as $result) {
                $repository = $result['owner'] . '/' . $result['name'];
                if (!in_array($repository, $repositories)) {
                    $repositories[] = $repository;
                }
            }
        }

        return array_slice($repositories, 0, $this->limit);
    }
}
