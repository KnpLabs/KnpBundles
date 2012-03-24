<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Git\Repo;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFetchAndResetWhenUpdate()
    {
        $gitRepo = $this->getPhpGitRepository();
        $gitRepo->expects($this->exactly(2))
            ->method('git');
        $gitRepo->expects($this->at(0))
            ->method('git')
            ->with($this->equalTo('fetch origin'));
        $gitRepo->expects($this->at(1))
            ->method('git')
            ->with($this->equalTo('reset --hard origin/HEAD'));

        $repo = new Repo(new Bundle(), $gitRepo);
        $repo->update();
    }

    /**
     * @test
     */
    public function shouldReturnsRequestedNumbersOfCommits()
    {
        $bundle = new Bundle();
        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBunldes');

        $gitRepo = $this->getPhpGitRepository();
        $gitRepo->expects($this->once())
            ->method('getCommits')
            ->with($this->equalTo('777'))
            ->will($this->returnValue($this->getCommits()));

        $expectedArray = array(
            '12344' => array('url' => 'http://github.com/KnpLabs/KnpBunldes/commit/12344', 'id' => '12344'),
            '33456' => array('url' => 'http://github.com/KnpLabs/KnpBunldes/commit/33456', 'id' => '33456')
        );

        $repo = new Repo($bundle, $gitRepo);
        $this->assertEquals($expectedArray, $repo->getCommits(777));
    }


    private function getPhpGitRepository()
    {
      return $this->getMockBuilder('PHPGit_Repository')
          ->disableOriginalConstructor()
          ->setMethods(array('git', 'getCommits'))
          ->getMock();
    }

    private function getCommits()
    {
        return array(
            '12344' => array('id' => '12344'),
            '33456' => array('id' => '33456')
        );
    }
}
