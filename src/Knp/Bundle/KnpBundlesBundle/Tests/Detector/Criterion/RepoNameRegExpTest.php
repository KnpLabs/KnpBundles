<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

require_once __DIR__ . '/CriterionTest.php';

class RepoNameRegExpTest extends CriterionTest
{
    /**
     * @dataProvider getMatches
     */
    public function testMatches($name, $regexp, $expected)
    {
        $repo = $this->getRepo('foobar');

        $entity = $repo->getRepoEntity();
        $entity->setName($name);

        $criterion = new RepoNameRegExp($regexp);

        $this->assertEquals($expected, $criterion->matches($repo));
    }

    public function getMatches()
    {
        return array(
            array('FooBarBundle', '/^(.+)Bundle$/', true),
            array('FooBar', '/^(.+)Bundle$/', false)
        );
    }
}
