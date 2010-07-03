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
        $output->writeln(sprintf('Will now search for new Bundles in GitHub'));

        $dm = $this->container->getDoctrine_odm_mongodb_documentManagerService();
        $existingBundles = $dm->find('Bundle\BundleStockBundle\Document\Bundle')->getResults();
        $githubRepos = $this->container->getGithubSearchService()->searchBundles();
        $validator = $this->container->getValidatorService();
        $bundles = array();
        $counters = array(
            'created' => 0,
            'updated' => 0,
            'removed' => 0
        );

        // first pass, update and revalidate existing bundles
        foreach($existingBundles as $existingBundle) {
            $exists = false;
            foreach($githubRepos as $githubRepo) {
                if($existingBundle->getName() === $githubRepo['name'] && $existingBundle->getUsername() === $githubRepo['username']) {
                    $existingBundle->fromRepositoryArray($githubRepo);
                    $exists = true;
                    ++$counters['updated'];
                    break;
                }
            }
            $existingBundle->setIsOnGithub($exists);
            if($validator->validate($existingBundle)->count()) {
                $dm->remove($existingBundle);
                ++$counters['removed'];
            }
        }
        
        // second pass, create missing bundles
        foreach($githubRepos as $githubRepo) {
            $exists = false;
            foreach($existingBundles as $existingBundle) {
                if($existingBundle->getName() === $githubRepo['name'] && $existingBundle->getUsername() === $githubRepo['username']) {
                    $exists = true;
                    break;
                }
            }
            if(!$exists) {
                $bundle = new Bundle();
                $bundle->fromRepositoryArray($githubRepo);
                $bundle->setIsOnGithub(true);
                if(!$validator->validate($bundle)->count()) {
                    $dm->persist($bundle);
                    ++$counters['created'];
                }
            }
        }

        $dm->flush();

        $output->writeln(sprintf('%d created, %d updated, %d removed', $counters['created'], $counters['updated'], $counters['removed']));
    }
}
