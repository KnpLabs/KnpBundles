<?php

namespace Knp\Bundle\KnpBundlesBundle\Finder;

/**
 * Base class for finders
 *
 * @package KnpBundles
 */
abstract class AbstractBaseFinder implements FinderInterface
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
}
