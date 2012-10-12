<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Updater;

require_once __DIR__.'../../../../../../../app/AppKernel.php';

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Knp\Bundle\KnpBundlesBundle\DataFixtures\ORM\Data;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

/**
 * @author Luis Cordova <cordoval@gmail.com>
 * @author Oscar Balladares <liebegrube@gmail.com>
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $em;

    public function setUp()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();

        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $states = array(
            Bundle::STATE_UNKNOWN,
            Bundle::STATE_NOT_YET_READY,
            Bundle::STATE_READY,
            Bundle::STATE_DEPRECATED
        );

        $purger = new ORMPurger($this->em);
        $purger->purge();

        $user = new Developer();
        $user->setName('John');

        $this->em->persist($user);

        $bundle = new Bundle();
        $bundle->fromArray(array(
            'name'          => 'TestBundleToBeRemoved',
            'ownerName'     => $user->getName().'test',
            'owner'         => $user,
            'description'   => 'Description of my bundle',
            'homepage'      => 'Bundle.com',
            'readme'        => 'the test bundle',
            'usesTravisCi'  => false,
            'composerName'  => 'knplabs/test-bundle',
            'state'         => $states[mt_rand(0, 3)],
            'travisCiBuildStatus'  => null,
            'nbFollowers'   => 10,
            'nbForks'       => 2,
            'lastCommitAt'  => new \DateTime('-'.(1*4).' day'),
            'isFork'        => false,
            'contributors'  => array($user)
        ));

        $this->em->persist($bundle);
        $this->em->flush();
    }

    public function tearDown()
    {
        $purger = new ORMPurger($this->em);
        $purger->purge();

        $fixtures = new Data();
        $fixtures->load($this->em);
    }

    /**
     * @test
     */
    public function shouldRemoveOneNonSymfonyBundle()
    {
        $invalidBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findOneBy(array('name' => 'TestBundleToBeRemoved'));
        $this->assertNotNull($invalidBundle);

        $updater = $this->getUpdater($invalidBundle );
        $updater->removeNonSymfonyBundles();

        $invalidBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findOneBy(array('name' => 'TestBundleToBeRemoved'));
        $this->assertNull($invalidBundle);
    }

    private function getUpdater($invalidBundle = null)
    {
        $updater = new Updater($this->em, $this->container->get('knp_bundles.bundle.manager'), $this->container->get('knp_bundles.finder'), $this->getRepoApi($invalidBundle));

        return $updater;
    }

    private function getRepoApi($invalidBundle = null)
    {
        $repoApi = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Github\Repo')
            ->disableOriginalConstructor()
            ->getMock();

        if ($invalidBundle) {
            $repoApi->expects($this->any())
                ->method('validate')
                ->with($this->equalTo($invalidBundle))
                ->will($this->returnValue(false));
        }

        return $repoApi;
    }
}
