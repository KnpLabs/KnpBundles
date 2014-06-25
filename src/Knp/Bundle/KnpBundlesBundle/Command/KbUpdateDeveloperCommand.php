<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KbUpdateDeveloperCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:update:developer')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Username of the Developer to update.'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Update all Developers'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /* @var $updater \Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdater */
        $updater = $container->get('knp_bundles.developer_updater');

        if ($name = $input->getArgument('name')) {
            $updater->updateDeveloperByName($name);
        }

        if ($input->getOption('all')) {
            $updater->updateAll();
        }
    }
}
