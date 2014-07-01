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
            ->addOption(
                'plain',
                null,
                InputOption::VALUE_NONE,
                'Use plain Developer update, bypass RabbitMQ'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /* @var $updaterManager \Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdaterManager */
        $updaterManager = $container->get('knp_bundles.developer_updater_manager');

        $updaterStrategy = $input->getOption('plain') ?
            $container->get('knp_bundles.developer_updater.strategy.plain') :
            $container->get('knp_bundles.developer_updater.strategy.rabbit_mq')
        ;
        $updaterManager->setUpdateStrategy($updaterStrategy);

        $updaterManager->setMessenger(function($developerName) use ($output) {
            $output->writeln(sprintf(
                    'Developer with username "%s" has been updated',
                    $developerName
                )
            );
        });

        if ($name = $input->getArgument('name')) {
            $updaterManager->updateDeveloperByName($name);
        }

        if ($input->getOption('all')) {
            $updaterManager->updateAll();
        }
    }
}
