<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Indexer\SolrIndexer;
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

        foreach ($bundles as $key => $bundle) {
            try {
                $indexer->indexBundle($bundle);
            } catch (Exception $e) {
                $output->writeln('<error>Exception: '.$e->getMessage().', skipping bundle '.$bundle->getFullName().'.</error>');
            }

            unset($bundles[$key]);
        }

        $doctrine->getManager()->flush();
    }
} 