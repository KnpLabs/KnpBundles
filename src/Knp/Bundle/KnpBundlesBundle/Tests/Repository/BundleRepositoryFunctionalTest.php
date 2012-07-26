<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Knp\Bundle\KnpBundlesBundle\Entity\User;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BundleRepositoryFunctionalTest extends WebTestCase
{
    private $em;
    protected $bundleRepository;
    protected $scoreRepository;
    protected $bundle1;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('knp_bundles.entity_manager');

        // initialize 3 bundles with 3 score entries each on Score table
        $this->bundleRepository = $this->em->getRepository('KnpBundlesBundle:Bundle');
        $this->scoreRepository = $this->em->getRepository('KnpBundlesBundle:Score');

        $user = new User();
        $user->setName('userExample');
        $user->setScore(1);
        $this->em->persist($user);
        $this->em->flush();

        $bundle1 = new Bundle('vendor/bundle1');
        $bundle1->setDescription('not null description');
        $bundle1->setUser($user);
        $bundle1->setScore(1);
        $score11 = $this->scoreRepository->setScore(new \DateTime('yesterday'), $bundle1, 16);
        $score12 = $this->scoreRepository->setScore(new \DateTime('today'), $bundle1, 45);
        $this->em->persist($score11);
        $this->em->persist($score12);
        $this->em->persist($bundle1);

        $bundle2 = new Bundle('vendor/bundle2');
        $bundle2->setDescription('not null description');
        $bundle2->setUser($user);
        $bundle2->setScore(2);
        $score21 = $this->scoreRepository->setScore(new \DateTime('yesterday'), $bundle2, 16);
        $score22 = $this->scoreRepository->setScore(new \DateTime('today'), $bundle2, 45);
        $this->em->persist($score21);
        $this->em->persist($score22);
        $this->em->persist($bundle2);

        $bundle3 = new Bundle('vendor/bundle3');
        $bundle3->setDescription('not null description');
        $bundle3->setUser($user);
        $bundle3->setScore(3);
        $score31 = $this->scoreRepository->setScore(new \DateTime('yesterday'), $bundle3, 16);
        $score32 = $this->scoreRepository->setScore(new \DateTime('today'), $bundle3, 45);
        $this->em->persist($score31);
        $this->em->persist($score32);
        $this->em->persist($bundle3);

        $this->em->flush();
    }

    public function testUpdateTrends()
    {
        $this->em->getConnection()->beginTransaction();

        $nbRows = $this->bundleRepository->updateTrends();
        $this->assertEquals(3, $nbRows);

        $this->em->getConnection()->commit();
        $this->em->clear();
        
        $bundle1 = $this->bundleRepository->findOneBy(array('name' => 'bundle1'));

        $this->assertEquals(29, $bundle1->getTrend1());
    }
}
