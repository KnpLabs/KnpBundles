<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KbSolrReindexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('kb:solr:reindex')
            ->setDescription('Removes all data from solr index and creates new index')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexer = $this->getContainer()->get('knp_bundles.indexer.solr');
        $indexer->deleteBundlesIndexes();

        $doctrine = $this->getContainer()->get('doctrine');
        $bundles = $doctrine->getRepository('KnpBundlesBundle:Bundle')->findAll();

        $count = count($bundles);
        foreach ($bundles as $key => $bundle) {
            try {
                $indexer->indexBundle($bundle);
                $this->printOutput($output, $key, $count);
            } catch (Exception $e) {
                $output->writeln(sprintf("<error>Exception: %s, skipping bundle %s.</error>", $e->getMessage(), $bundle->getFullName()));
            }

            unset($bundles[$key]);
        }
    }

    private function printOutput($output, $key, $count)
    {
        $percent = round($key / $count * 100, 2);
        $output->write(sprintf(" %.2f%% [", $percent));
        for ($i = 0; $i < 100; $i++) {
            if ($i == round($percent)) {
                $output->write('>');
            } elseif ($i < round($percent)) {
                $output->write('=');
            } else {
                $output->write('.');
            }
        }
        $output->write("] $key of $count\r");
    }
}
