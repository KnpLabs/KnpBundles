<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

abstract class CriterionTest extends \PHPUnit_Framework_TestCase
{
    protected function getRepoEntity()
    {
        return $this->getMockForAbstractClass('Knp\Bundle\KnpBundlesBundle\Entity\Repo');
    }

    protected function getGitRepo($directory)
    {
        $repo = $this->getMock('PHPGit_Repository', array(), array(), '', false);
        $repo->expects($this->any())
            ->method('getDir')
            ->will($this->returnValue($directory));

        return $repo;
    }

    protected function getRepo($directory)
    {
        return $this->getMockForAbstractClass(
            'Knp\Bundle\KnpBundlesBundle\Git\Repo',
            array(
                $this->getRepoEntity(),
                $this->getGitRepo($directory)
            )
        );
    }
}
