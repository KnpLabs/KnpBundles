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
        $solarium = $this->getContainer()->get('solarium.client');

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
                $output->writeln('<info>[Edgar]</info>: Deleting existing index.');
            }

            $update = $solarium->createUpdate();
            $update->addDeleteQuery('*:*');
            $update->addCommit();

            $solarium->update($update);
        }

        foreach ($bundles as $bundle) {
            if ($verbose) {
                $output->writeln('<info>[Edgar]</info>: Indexing '.$bundle->getFullName().'...');
            }

            try {
                $update = $solarium->createUpdate();
                $document = $update->createDocument();
                $this->updateDocumentFromBundle($document, $bundle);
                $update->addDocument($document);
                $update->addCommit();
                $solarium->update($update);
                $bundle->setIndexedAt(new \DateTime);
            } catch (\Exception $e) {
                $output->writeln('<info>[Edgar]</info>: <error>Exception: '.$e->getMessage().', skipping bundle '.$bundle->getFullName().'.</error>');
            }
        }

        $doctrine->getEntityManager()->flush();
    }

    private function updateDocumentFromBundle(\Solarium_Document_ReadWrite $document, Bundle $bundle)
    {
        $document->id = $bundle->getId();
        $document->name = $bundle->getName();
        $document->username = $bundle->getUsername();
        $document->fullName = $bundle->getFullName();
        $document->description = $bundle->getDescription();
        $document->totalScore = $bundle->getScore();
        $document->userGravatarHash = $bundle->getUser()->getGravatarHash();

        // Hacky hack to format date until this is merged to master branch.
        // https://github.com/basdenooijer/solarium/pull/62/files.
        $iso8601 = $bundle->getLastCommitAt()->format(\DateTime::ISO8601);
        $iso8601 = strstr($iso8601, '+', true);
        $iso8601 .= 'Z';

        $document->lastCommitAt = $iso8601;

        $keywords = array();
        foreach ($bundle->getKeywords() as $keyword) {
            $keywords[] = $keyword->getValue();
        }
        $document->keywords = $keywords;
    }
}