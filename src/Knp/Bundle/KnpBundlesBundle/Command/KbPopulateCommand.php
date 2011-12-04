<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

/**
 * Update local database from web searches
 */
class KbPopulateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'The maximal number of new bundles considered by the update', 1000)
            ->setName('kb:populate')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('knp_bundles.entity_manager');

        $updater = $this->getContainer()->get('knp_bundles.updater');
        $updater->setOutput($output);
        $updater->setUp();

        $bundles = $updater->searchNewBundles((int) $input->getOption('limit'));
        $updater->createMissingBundles($bundles);
        $em->flush();
        $updater->updateBundlesData();
        $em->flush();
        $updater->updateUsers();
        $em->flush();
    }
}
