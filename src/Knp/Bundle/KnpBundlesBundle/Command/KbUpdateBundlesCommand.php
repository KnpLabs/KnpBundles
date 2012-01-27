<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

class KbUpdateBundlesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addOption('no-publish', null, InputOption::VALUE_NONE, 'Prevent the command from publishing a message to RabbitMQ producer')
            ->setName('kb:update:bundles')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $updater = $container->get('knp_bundles.updater');

        if (!$input->getOption('no-publish')) {
            // manually set RabbitMQ producer
            $updater->setBundleUpdateProducer($container->get('old_sound_rabbit_mq.update_bundle_producer'));
        }

        $updater->setOutput($output);
        $updater->setUp();

        $updater->updateBundlesData();

        $em = $this->getContainer()->get('knp_bundles.entity_manager');
        $em->flush();
    }
}
