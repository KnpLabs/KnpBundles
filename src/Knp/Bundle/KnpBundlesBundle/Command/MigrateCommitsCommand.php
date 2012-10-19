<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Github\Repo;

/**
 * @todo Remove this command on next week or so
 */
class MigrateCommitsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:migrate:commits')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $repo Repo */
        $em    = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo  = $this->getContainer()->get('knp_bundles.github_repository_api');

        $page  = 1;
        $pager = new Pagerfanta(new DoctrineORMAdapter($em->getRepository('KnpBundlesBundle:Bundle')->queryAllSortedBy('updatedAt'), false));
        $pager
            ->setMaxPerPage(100)
            ->setCurrentPage($page, false, true)
        ;

        if (1 === $page) {
            $output->writeln(sprintf('[%s] Loaded <comment>%d</comment> bundles from the DB', date('d-m-y H:i:s'), $pager->getNbResults()));
        }

        do {
            /** @var $bundle Bundle */
            foreach ($pager->getCurrentPageResults() as $bundle) {
                // Check that API not failed
                if (!$repo->updateCommits($bundle)) {
                    // Sleep a while, and check again
                    sleep(60);

                    $repo->updateCommits($bundle);
                }
            }

            $output->writeln(sprintf('[%s] Migrated %d from %d  bundles', date('d-m-y H:i:s'), $page * 100, $pager->getNbResults()));

            ++$page;
        } while ($pager->hasNextPage() && $pager->setCurrentPage($page, false, true));

        return 0;
    }
}
