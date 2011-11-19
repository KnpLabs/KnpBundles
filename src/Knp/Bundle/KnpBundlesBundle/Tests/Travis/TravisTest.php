<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Travis;

use Knp\Bundle\KnpBundlesBundle\Git\RepoManager;
use Knp\Bundle\KnpBundlesBundle\Entity\Repo as RepoEntity;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;
use Symfony\Component\Console\Output\OutputInterface;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTravisDataSuccess()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $travis = new Travis($output);
    
        $method = new \ReflectionMethod($travis, 'getTravisData');
        $method->setAccessible(true);
    
        $travisData = $method->invokeArgs($travis, array('travis-ci/travis-hub'));
        $this->assertArrayHasKey('last_build_status', $travisData);
        $this->assertArrayHasKey('slug', $travisData);
        $this->assertEquals('travis-ci/travis-hub', $travisData['slug']);
    }

    public function testGetTravisDataFailure()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $travis = new Travis($output);

        $method = new \ReflectionMethod($travis, 'getTravisData');
        $method->setAccessible(true);

        $travisData = $method->invokeArgs($travis, array('travis-ci/loremipsumdolor'));
        $this->assertEquals(null, $travisData);
    }

    public function testGetRepositoryStatus()
    {
        $travisData = array('check' => 'check');

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $travis = $this->getMock('Knp\Bundle\KnpBundlesBundle\Travis\Travis',
            array('getTravisData'),
            array($output)
        );
        $travis->expects($this->any())
            ->method('getTravisData')
            ->with($this->equalTo('lorem/ipsum'))
            ->will($this->returnValue($travisData));

        $repo = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Repo', array('getName', 'getUsername'));
        $repo->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('lorem'));
        $repo->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('ipsum'));

        $method = new \ReflectionMethod($travis, 'getTravisDataForRepo');
        $method->setAccessible(true);

        $travisData = $method->invokeArgs($travis, array($repo));
        $this->assertEquals($travisData, $travisData);
    }

    public function testUpdatePassing()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $travis = $this->getMock('Knp\Bundle\KnpBundlesBundle\Travis\Travis',
            array('getTravisDataForRepo'),
            array($output)
        );
        $travisData = array('last_build_status' => '0');
        $travis->expects($this->any())
            ->method('getTravisDataForRepo')
            ->will($this->returnValue($travisData));

        $repo = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Repo', array('setTravisCiBuildStatus'));
        $repo->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with(true);

        $travis->update($repo);
    }

    public function testUpdateFailed()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $travis = $this->getMock('Knp\Bundle\KnpBundlesBundle\Travis\Travis',
            array('getTravisDataForRepo'),
            array($output)
        );
        $travisData = array('last_build_status' => '1');
        $travis->expects($this->any())
            ->method('getTravisDataForRepo')
            ->will($this->returnValue($travisData));

        $repo = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Repo', array('setTravisCiBuildStatus'));
        $repo->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with(false);

        $travis->update($repo);
    }

    public function testUpdateError()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $travis = $this->getMock('Knp\Bundle\KnpBundlesBundle\Travis\Travis',
            array('getTravisDataForRepo'),
            array($output)
        );
        $travisData = null;
        $travis->expects($this->any())
            ->method('getTravisDataForRepo')
            ->will($this->returnValue($travisData));

        $repo = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Repo', array('setTravisCiBuildStatus'));
        $repo->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with(null);

        $travis->update($repo);
    }
}
