<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Travis;

use Knp\Bundle\KnpBundlesBundle\Git\RepoManager;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle as BundleEntity;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Leszek Prabucki <leszek.prabucki@knplabs.com>
 */
class RepoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldUpdateBundleForSuccessfulBuildStatus()
    {
        $travis = $this->getTravis();
        $travis->expects($this->once())
            ->method('getTravisData')
            ->with($this->equalTo('KnpLabs/KnpBundles'))
            ->will($this->returnValue(array('last_build_status' => 0)));

        $bundle = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Bundle', array('setTravisCiBuildStatus'));
        $bundle->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with($this->isTrue());

        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $travis->update($bundle);
    }

    /**
     * @test
     */
    public function shouldUpdateBundleForFailureBuildStatus()
    {
        $travis = $this->getTravis();
        $travis->expects($this->once())
            ->method('getTravisData')
            ->with($this->equalTo('KnpLabs/KnpBundles'))
            ->will($this->returnValue(array('last_build_status' => 1)));

        $bundle = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Bundle', array('setTravisCiBuildStatus'));
        $bundle->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with($this->isFalse());

        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $travis->update($bundle);
    }

    /**
     * @test
     */
    public function shouldUpdateBundleForUndefinedBuildStatus()
    {
        $travis = $this->getTravis();
        $travis->expects($this->once())
            ->method('getTravisData')
            ->will($this->returnValue(array('last_build_status' => 777)));

        $bundle = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Bundle', array('setTravisCiBuildStatus'));
        $bundle->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with($this->isNull());

        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $travis->update($bundle);
    }

    /**
     * @test
     */
    public function shouldUpdateBundleWhenCannotFetchStatus()
    {
        $travis = $this->getTravis();
        $travis->expects($this->once())
            ->method('getTravisData')
            ->will($this->returnValue(array()));

        $bundle = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Bundle', array('setTravisCiBuildStatus'));
        $bundle->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with($this->isNull());

        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $travis->update($bundle);
    }

    private function getTravis()
    {
        $output  = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $browser = $this->getMock('Buzz\Browser');

        return $this->getMock('Knp\Bundle\KnpBundlesBundle\Travis\Travis',
            array('getTravisData'),
            array($output, $browser)
        );
    }
}
