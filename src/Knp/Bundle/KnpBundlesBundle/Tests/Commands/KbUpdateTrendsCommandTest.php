<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Commands;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Knp\Bundle\KnpBundlesBundle\Entity\User;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Command\KbUpdateTrendsCommand;

use Doctrine\ORM\Tools\SchemaTool;

class KbUpdateTrendsCommandTest extends WebTestCase
{
    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        if ($kernel->getContainer()->getParameter('database_driver') == 'pdo_sqlite') {
            $this->markTestSkipped(
                "The SQLite does not support joins."
            );
        }

        $em = $kernel->getContainer()->get('knp_bundles.entity_manager');
        $scoreRepository = $em->getRepository('KnpBundlesBundle:Score');

        $fileLocator = new FileLocator(__DIR__ . '/fixtures/');
        $path = $fileLocator->locate('trending-bundles.yml');
        $data = Yaml::parse($path);

        $user = new User();
        $user->setName('someName');
        $user->setScore(0);

        $em->persist($user);

        foreach ($data['bundles'] as $bundleName => $bundleData) {
            $bundle = new Bundle('vendor/' . $bundleName);
            $bundle->setDescription('some description');
            $bundle->setScore(100);
            $bundle->setUser($user);

            foreach ($bundleData['scores'] as $scoreData) {
                $score = $scoreRepository->setScore(new \DateTime($scoreData['date']), $bundle, $scoreData['value']);
                $em->persist($score);
            }

            $em->persist($bundle);
            $em->flush();
        }
    }

    public function testExecute()
    {
        $client = $this->createClient();

        $application = new Application($client->getKernel());
        $application->add(new KbUpdateTrendsCommand());

        $command = $application->find('kb:update:trends');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/3 rows updated/', $commandTester->getDisplay());
    }
}
