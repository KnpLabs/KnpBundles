<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

class KbAddBundleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addArgument('bundleName')
            ->setName('kb:add:bundle')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = $this->getContainer()->get('knp_bundles.updater');
        $updater->setOutput($output);
        $updater->setUp();

        $bundles = $updater->addBundle($input->getArgument('bundleName'));

        $em = $this->getContainer()->get('knp_bundles.entity_manager');
        $em->flush();
    }
}
