<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector\Criterion;

use Knp\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * A stub criterion for test purposes
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
 */
class Stub implements CriterionInterface
{
    private $result;

    /**
     * Constructor
     *
     * @param  Boolean $result
     */
    public function __construct($result)
    {
        $this->setResult($result);
    }

    /**
     * Defines the result
     *
     * @param  Boolean $result
     */
    public function setResult($result)
    {
        $this->result = (Boolean) $result;
    }

    /**
     * Return the result
     *
     * @return Boolean
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        return $this->result;
    }
}
