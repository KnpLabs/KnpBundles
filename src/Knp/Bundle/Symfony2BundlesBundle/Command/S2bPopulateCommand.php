<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\Symfony2BundlesBundle\Updater\Updater;

/**
 * Update local database from web searches
 */
class S2bPopulateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('s2b:populate')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gitRepoDir = $this->getContainer()->getParameter('kernel.root_dir').'/repos';
        $gitBin = $this->getContainer()->getParameter('knp_symfony2bundles.git_bin');

        $em = $this->getContainer()->get('knp_symfony2bundles.entity_manager');

        $updater = new Updater($em, $gitRepoDir, $gitBin, $output);
        $updater->setUp();
        $repos = $updater->searchNewRepos(1000);
        $updater->createMissingRepos($repos);
        $em->flush();
        $updater->updateReposData();
        $em->flush();
        $updater->updateUsers();
        $em->flush();
    }
}
