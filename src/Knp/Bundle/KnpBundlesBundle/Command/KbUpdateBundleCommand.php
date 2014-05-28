<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

class KbUpdateBundleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:update:bundle')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the bundle you want to update. owner/name'
            )
            ->addOption(
                'rabbitmq',
                null,
                InputOption::VALUE_NONE,
                'Using rabbitmq if specified'
            )
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

        /* @var $updater Updater */
        $updater = $container->get('knp_bundles.updater');
        $updater->setOutput($output);

        list($owner, $name) = explode('/', $input->getArgument('name'));

        if ($input->getOption('rabbitmq')) {
            // manually set RabbitMQ producer
            $updater->setBundleUpdateProducer($container->get('old_sound_rabbit_mq.update_bundle_producer'));
        } else {
            $updater->setBundleUpdateConsumer($container->get('knp_bundles.consumer.update_bundle'));
        }

        $updater->updateBundleData($owner, $name);

        $em = $container->get('knp_bundles.entity_manager');
        $em->flush();
    }
}
