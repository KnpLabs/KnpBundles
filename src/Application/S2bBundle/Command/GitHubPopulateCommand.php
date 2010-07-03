<?php

namespace Application\S2bBundle\Command;

use Bundle\BundleStockBundle\Document\Bundle;
use Symfony\Framework\FoundationBundle\Command\Command as BaseCommand;
use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;

/**
 * Update local database from GitHub searches
 */
class GitHubPopulateCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        ->setDefinition(array())
        ->setName('github:populate');
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Will now search new Bundles in GitHub'));

        $search = $this->container->getGithubSearchService();
<<<<<<< HEAD
        $bundles = array();
        
        foreach($search->searchBundles(300) as $repo) {
            $bundle = new Bundle();
            $bundle->setName($repo['name']);
            $bundle->setAuthor($repo['username']);
            $bundles[] = $bundle;
        }

        $bundles = $this->filterValidBundles($bundles);
=======
        $bundles = $search->searchBundles(300);
>>>>>>> 59a9800... Improve bundle search

        foreach($bundles as $bundle) {
            $output->writeln($bundle->getGitHubUrl());
        }

        $output->writeln(sprintf('%d Bundles found', count($bundles)));
    }

    /**
     * Returns only valid Symfony2 bundles
     *
     * @return array
     **/
    protected function filterValidBundles(array $bundles)
    {
        $validator = $this->container->getValidatorService();
        foreach($bundles as $bundle) {
            print $validator->validate($bundle);
        }
        return $bundles;
    }
}
