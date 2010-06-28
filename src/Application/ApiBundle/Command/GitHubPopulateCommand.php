<?php

namespace Application\ApiBundle\Command;

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
            ->setDefinition(array(
            ))
            ->setName('s2b:github:populate')
        ;
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
        $bundles = $search->searchBundles();

        foreach($bundles as $bundle) {
            $output->writeln($bundle['name']);
        }

        $output->writeln(sprintf('%d Bundles found', count($bundles)));
    }
}
