<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Updater;

require_once __DIR__.'../../../../../../../app/AppKernel.php';

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle as BundleEntity;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\DataFixtures\ORM\Data;

/**
 * @author Luis Cordova <cordoval@gmail.com>
 * @author Oscar Balladares <liebegrube@gmail.com>
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    protected $githubRepoApi;
    protected $container;
    protected $em;

    public function setUp()
    {
        $this->markTestIncomplete('This needs to be updated to new code.');

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

        $fixtures = new Data();
        $fixtures->load($this->em);

        $user = $this->em->getRepository('KnpBundlesBundle:User')->findOneBy(array('name' => 'John'));

        $bundle = new Bundle();
        $bundle->fromArray(array(
            'name'          => 'TestBundleToBeRemoved',
            'username'      => $user->getName().'test',
            'user'          => $user,
            'description'   => 'Description of my bundle',
            'homepage'      => 'Bundle.com',
            'readme'        => "the test bundle",
            'tags'          => array('1.0', '1.1'),
            'usesTravisCi'  => false,
            'composerName'  => 'knplabs/test-bundle',
            'state'         => $states[mt_rand(0, 3)],
            'travisCiBuildStatus'  => null,
            'nbFollowers'   => 10,
            'nbForks'       => 2,
            'lastCommitAt'  => new \DateTime('-'.(1*4).' day'),
            'lastCommits'   => array(
                array(
                    'commit' => array(
                        'author'    => array(
                            'date'  => '2010-05-16T09:58:32-09:00',
                            'name'  => $user->getFullName(),
                            'email' => $user->getEmail()
                        ),
                        'committer' => array(
                            'date'  => '2010-05-16T09:58:32-09:00',
                            'name'  => $user->getFullName(),
                            'login' => $user->getName()
                        ),
                        'url'       => 'http://github.com',
                        'message'   => 'Fix something on this Bundle',
                    ),
                ),
                array(
                    'commit' => array(
                        'author'    => array(
                            'date'  => '2010-05-16T09:58:32-07:00',
                            'name'  => $user->getFullName(),
                            'email' => $user->getEmail()
                        ),
                        'committer' => array(
                            'date'  => '2010-05-16T09:58:32-07:00',
                            'name'  => $user->getFullName(),
                            'email' => $user->getEmail()
                        ),
                        'url'       => 'http://github.com',
                        'message'   => 'Commit something on this bundle',
                    ),
                ),
            ),
            'isFork'        => false,
            'contributors'  => array($user)
        ));

        $this->em->persist($bundle);
        $this->em->flush();
    }

    /**
     * @test
     */
    public function shouldRemoveOneNonSymfonyBundle()
    {
        $this->githubRepoApi = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Github\Repo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->githubRepoApi
            ->expects($this->any())
            ->method('updateFiles')
            ->will($this->returnValue(false));

        $userManager = $this->container->get('knp_bundles.user.manager');
        $finder = $this->container->get('knp_bundles.finder');
        $githubUsers = $this->container->get('knp_bundles.github.users');

        $updater = new Updater($this->em, $userManager, $finder, $githubUsers, $this->githubRepoApi);

        $shouldNotBeNull = $this->em->getRepository('KnpBundlesBundle:Bundle')->findOneBy(array('name' => 'TestBundleToBeRemoved'));

        $this->assertNotNull($shouldNotBeNull);

        $updater->removeNonSymfonyBundles();

        $shouldBeNull = $this->em->getRepository('KnpBundlesBundle:Bundle')->findOneBy(array('name' => 'TestBundleToBeRemoved'));

        $this->assertNull($shouldBeNull);
    }
}
