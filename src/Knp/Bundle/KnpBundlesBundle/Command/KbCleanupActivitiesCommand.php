<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

class KbCleanupActivitiesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('kb:cleanup:activities')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'how many leave activities', 30)
            ->setDescription('Clean up activities table to stay <LIMIT> latest bundles activities.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $updater Updater */
        $updater = $this->getContainer()->get('knp_bundles.updater');
        $updater->setOutput($output);

        $updater->cleanupBundlesActivities($input->getOption('limit'));
    }
}
