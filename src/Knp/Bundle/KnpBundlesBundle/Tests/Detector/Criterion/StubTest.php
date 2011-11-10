<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

require_once __DIR__ . '/CriterionTest.php';

class StubTest extends CriterionTest
{
    public function testMatches()
    {
        $repo = $this->getRepo('foobar');

        $criterion = new Stub(false);

        $this->assertFalse($criterion->matches($repo));

        $criterion = new Stub(true);

        $this->assertTrue($criterion->matches($repo));
    }
}
