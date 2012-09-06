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
        $travis = $this->getTravis(array('last_build_status' => 0), 'KnpLabs/KnpBundles');

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
        $travis = $this->getTravis(array('last_build_status' => 1), 'KnpLabs/KnpBundles');

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
        $travis = $this->getTravis(array('last_build_status' => 777));

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
        $travis = $this->getTravis(array(), false);

        $bundle = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Bundle', array('setTravisCiBuildStatus'));
        $bundle->expects($this->once())
            ->method('setTravisCiBuildStatus')
            ->with($this->isNull());

        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $travis->update($bundle);
    }

    private function getTravis($return = array(), $with = null)
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $travis = new Travis($output);
        $travis->setBrowser($this->getBrowserMock($return, $with));

        return $travis;
    }

    private function getBrowserMock($return = array(), $with = null)
    {
        $client = $this->getMockForAbstractClass('Buzz\Client\Curl');
        $client->expects($this->any())
            ->method('setVerifyPeer')
            ->with($this->equalTo(false));
        $client->expects($this->any())
            ->method('setTimeout')
            ->with($this->equalTo(30));

        $response = $this->getMock('Buzz\Message\Response');
        $response->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue(false !== $with ? json_encode($return) : null));

        $browser = $this->getMock('Buzz\Browser');
        $browser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));
        $browser->expects($this->any())
            ->method('setClient')
            ->with($client);

        if (empty($with)) {
            $browser->expects($this->any())
                ->method('get')
                ->will($this->returnValue($response));
        } else {
            $browser->expects($this->any())
                ->method('get')
                ->with($this->equalTo('http://travis-ci.org/'.$with.'.json'))
                ->will($this->returnValue($response));
        }

        return $browser;
    }
}
