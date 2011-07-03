<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector\Criterion;

use Knp\Bundle\Symfony2BundlesBundle\Git\Repo;

/**
 * Criterion that handles some child criteria and takes a decision based on
 * their results and the configured strategy
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
 */
class Collection implements CriterionInterface
{
    const STRATEGY_MAJORITY = 'majority';
    const STRATEGY_ANY      = 'any';
    const STRATEGY_ALL      = 'all';

    private $strategy;
    private $criteria;

    /**
     * Constructor
     *
     * @param  string $strategy The strategy must be either STRATEGY_MAJORITY,
     *                          STRATEGY_ANY or STRATEGY_ALL
     * @param  array  $criteria An optional array of criteria
     */
    public function __construct($strategy, array $criteria = array())
    {
        $this->strategy = $strategy;
        $this->setCriteria($criteria);
    }

    /**
     * Defines the criteria. It clears the current list
     *
     * @param  array $criteria An array of criteria
     */
    public function setCriteria(array $criteria)
    {
        $this->criteria = array();
        foreach ($criteria as $criterion) {
            $this->addCriterion($criterion);
        }
    }

    /**
     * Adds a criterion
     *
     * @param  CriterionInterface $criterion A CriterionInterface instance
     */
    public function addCriterion(CriterionInterface $criterion)
    {
        $this->criteria[] = $criterion;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        $score = 0;
        foreach ($this->criteria as $criterion) {
            $result = $criterion->matches($repo);
            switch ($this->strategy) {
                case self::STRATEGY_ALL:
                    if (!$result) {
                        return false;
                    }
                case self::STRATEGY_MAJORITY:
                    $score += $result ? 1 : -1;
                    break;
                case self::STRATEGY_ANY:
                    if ($result) {
                        return true;
                    }
                    break;
            }
        }

        return $score > 0;
    }
}
