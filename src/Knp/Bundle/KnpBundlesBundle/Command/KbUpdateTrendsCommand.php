<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;
use Doctrine\ORM\Query\ResultSetMapping;

class KbUpdateTrendsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:update:trends')
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

        $bundleRepository = $em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        $em->getConnection()->beginTransaction();
        try {
            $nbRows = $bundleRepository->updateTrends();
            $output->writeln(sprintf('<info>%s</info> rows updated', $nbRows));

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Rollbacking</error> because of %s', $e));
            $em->getConnection()->rollback();
            $em->close();
        }

    }
}
