<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Indexer\SolrIndexer;
use Pagerfanta\Adapter\SolariumAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Auto remove index which doesn't exists.
 *
 * This should not happend in a standard process...
 * But it can happend if solr is down when the delete process
 * is executed.
 *
 * @author Nek (Maxime Veber) <nek.dev@gmail.com>
 */
class KbSolrRemoveFromIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:solr:remove-from-index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SolrIndexer $indexer */
        $indexer    = $this->getContainer()->get('knp_bundles.indexer.solr');
        /** @var EntityManager $em */
        $em         = $this->getContainer()->get('doctrine')->getManager();
        /** @var \Solarium_Client $solarium */
        $solarium   = $this->getContainer()->get('solarium.client');
        /** @var EntityRepository $repository */
        $repository = $em->getRepository('KnpBundlesBundle:Bundle');

        $query = $solarium->createSelect();
        $query->setFields(array('name', 'ownerName'));

        try {
            $hasMoreResults = true;
            $page           = 1;

            while ($hasMoreResults) {

                $paginator = new Pagerfanta(new SolariumAdapter($solarium, $query));
                $paginator
                    ->setMaxPerPage(50)
                    ->setCurrentPage($page, false, true)
                ;

                foreach ($paginator as $bundle) {
                    $entity = $repository->findOneBy(array('name' => $bundle['name']));
                    if (!$entity) {
                        $entity = new Bundle();
                        $entity->setName($bundle['name']);
                        $entity->setOwnerName($bundle['ownerName']);

                        $indexer->deleteBundlesIndexes($entity);
                        $output->writeln(sprintf('The bundle "%s" was deleted from solr index.', $entity->getFullName()));
                    }
                }

                $hasMoreResults = $paginator->getNbResults() == 50;
                $page++;
            }

        } catch (\Solarium_Client_HttpException $e) {
            throw new \Exception('Seems that our search engine is currently offline. Please check later.');
        }
    }
}
