<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('knp_bundles.entity_manager');
        if ($em->getConnection()->getDatabasePlatform()->getName() == 'sqlite') {
            $output->writeln(sprintf('[%s] This command can\'t be executed on <error>SQLite</error>!', date('d-m-y H:i:s')));

            return 1;
        }

        $em->getConnection()->beginTransaction();

        try {
            $nbRows = $this->getContainer()->get('knp_bundles.bundle.manager')->updateTrends();

            $em->getConnection()->commit();

            $output->writeln(sprintf('[%s] <info>%s</info> rows updated', date('d-m-y H:i:s'), $nbRows));
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $em->close();

            $output->writeln(sprintf('[%s] <error>Rollbacking</error> because of error: %s', date('d-m-y H:i:s'), $e));

            return 1;
        }

        return 0;
    }
}
