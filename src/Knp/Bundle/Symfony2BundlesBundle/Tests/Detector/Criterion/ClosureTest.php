<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector\Criterion;

require_once __DIR__ . '/CriterionTest.php';

class ClosureTest extends CriterionTest
{
    /**
     * @dataProvider getMatches
     */
    public function testMatches($closure, $expected)
    {
        $repo = $this->getRepo('foobar');

        $criterion = new Closure($closure);

        $this->assertEquals($expected, $criterion->matches($repo));
    }

    public function testRepoIsPassedToTheClosureAsFirstParameter()
    {
        $repo = $this->getRepo('foobar');
        $self = $this;
        $criterion = new Closure(function($param) use($repo, $self) {
            $self->assertEquals($repo, $param);
            return $param === $repo;
        });

        $this->assertTrue($criterion->matches($repo));
    }

    public function getMatches()
    {
        return array(
            array(
                function($repo) { return true; },
                true
            ),
            array(
                function($repo) { return false; },
                false
            )
        );
    }
}
