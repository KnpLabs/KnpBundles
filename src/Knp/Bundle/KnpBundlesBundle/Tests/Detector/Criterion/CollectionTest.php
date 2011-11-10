<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

require_once __DIR__ . '/CriterionTest.php';

class CollectionTest extends CriterionTest
{
    /**
     * @dataProvider getMatches
     */
    public function testMatches($strategy, $criteria, $expected)
    {
        $repo = $this->getRepo('fooman');

        $criterion = new Collection($strategy, $criteria);

        $this->assertEquals($expected, $criterion->matches($repo));
    }

    public function getMatches()
    {
        return array(
            array(
                Collection::STRATEGY_MAJORITY,
                array(
                    new Stub(true),
                    new Stub(true),
                    new Stub(false)
                ),
                true
            ),
            array(
                Collection::STRATEGY_MAJORITY,
                array(
                    new Stub(true),
                    new Stub(false)
                ),
                false
            ),
            array(
                Collection::STRATEGY_ALL,
                array(
                    new Stub(true),
                    new Stub(true),
                    new Stub(true)
                ),
                true
            ),
            array(
                Collection::STRATEGY_ALL,
                array(
                    new Stub(true),
                    new Stub(false),
                    new Stub(true)
                ),
                false
            ),
            array(
                Collection::STRATEGY_ANY,
                array(
                    new Stub(true),
                    new Stub(false)
                ),
                true
            ),
            array(
                Collection::STRATEGY_ANY,
                array(
                    new Stub(false),
                    new Stub(false)
                ),
                false
            )
        );
    }
}
