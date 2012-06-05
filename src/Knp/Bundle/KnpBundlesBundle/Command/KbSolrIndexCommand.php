<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class KbSolrIndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('kb:solr:index')
            ->setDefinition(array(
                new InputOption('force', null, InputOption::VALUE_NONE, 'Force a re-indexing of all bundles'),
                new InputArgument('bundleName', InputArgument::OPTIONAL, 'Bundle name to index'),
            ))
            ->setDescription('Indexes bundles in Solr')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');
        $force = $input->getOption('force');
        $bundleName = $input->getArgument('bundleName');

        $doctrine = $this->getContainer()->get('doctrine');
        $indexer = $this->getContainer()->get('knp_bundles.indexer.solr');

        if ($bundleName) {
            list($username, $name) = explode('/', $bundleName);
            $bundles = array($doctrine->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\Bundle')->findOneByUsernameAndName($username, $name));
        } elseif ($force) {
            $bundles = $doctrine->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\Bundle')->findAll();
        } else {
            $bundles = $doctrine->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\Bundle')->getStaleBundlesForIndexing();
        }

        if ($force && !$bundleName) {
            if ($verbose) {
                $output->writeln('Deleting existing index.');
            }
            
            $indexer->deleteBundlesIndexes();
        }

        foreach ($bundles as $bundle) {
            if ($verbose) {
                $output->writeln('Indexing '.$bundle->getFullName().'...');
            }

            try {
                $indexer->indexBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln('<error>Exception: '.$e->getMessage().', skipping bundle '.$bundle->getFullName().'.</error>');
            }
        }
        $doctrine->getEntityManager()->flush();
    }
}
