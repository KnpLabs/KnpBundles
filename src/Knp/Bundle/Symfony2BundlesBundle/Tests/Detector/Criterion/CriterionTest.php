<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Detector\Criterion;

abstract class CriterionTest extends \PHPUnit_Framework_TestCase
{
    protected function getRepoEntity()
    {
        return $this->getMockForAbstractClass('Knp\Bundle\Symfony2BundlesBundle\Entity\Repo');
    }

    protected function getGitRepo($directory)
    {
        $repo = $this->getMock('phpGitRepo', array(), array(), '', false);
        $repo->expects($this->any())
            ->method('getDir')
            ->will($this->returnValue($directory));

        return $repo;
    }

    protected function getRepo($directory)
    {
        return $this->getMockForAbstractClass(
            'Knp\Bundle\Symfony2BundlesBundle\Git\Repo',
            array(
                $this->getRepoEntity(),
                $this->getGitRepo($directory)
            )
        );
    }
}
