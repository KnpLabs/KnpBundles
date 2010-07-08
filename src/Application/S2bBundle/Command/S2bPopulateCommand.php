<?php

namespace Application\S2bBundle\Command;

use Application\S2bBundle\Document\Bundle;
use Application\S2bBundle\Document\User;
use Application\S2bBundle\Github;
use Symfony\Framework\FoundationBundle\Command\Command as BaseCommand;
use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;

// Require Goutte
require_once(__DIR__.'/../../../vendor/Goutte/src/Goutte/Client.php');

// Ugly fix to prevent Zend fatal error
set_include_path(get_include_path().PATH_SEPARATOR.realpath(__DIR__.'/../../../vendor/Zend/library'));

/**
 * Update local database from web searches
 */
class S2bPopulateCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        ->setDefinition(array())
        ->setName('s2b:populate');
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $github = new \phpGitHubApi();
        $githubSearch = new Github\Search($github, new \Goutte\Client(), $output);
        $githubUser = new Github\User($github, $output);
        $githubBundle = new Github\Bundle($github, $output);

        $githubRepos = $githubSearch->searchBundles(5000, $output);
        $output->writeLn(sprintf('Found %d bundle candidates', count($githubRepos)));

        $dm = $this->container->getDoctrine_odm_mongodb_documentManagerService();
        $existingBundles = $dm->find('Application\S2bBundle\Document\Bundle')->getResults();
        $users = $dm->find('Application\S2bBundle\Document\User')->getResults();
        $validator = $this->container->getValidatorService();
        $bundles = array();
        $counters = array(
            'created' => 0,
            'updated' => 0,
            'removed' => 0
        );

        // first pass, update and revalidate existing bundles
        foreach($existingBundles as $existingBundle) {
            $existingBundle->setIsOnGithub(false);
            foreach($githubRepos as $githubRepo) {
                if($existingBundle->getName() === $githubRepo['name'] && $existingBundle->getUsername() === $githubRepo['username']) {
                    $githubBundle->updateInfos($existingBundle);
                    break;
                }
            }
            if(!$existingBundle->getIsOnGithub()) {
                $output->writeLn(sprintf('Remove %s : no more on Github', $existingBundle->getFullName()));
                ++$counters['removed'];
                $dm->remove($existingBundle);
                continue;
            }

            // if the bundles doesnt validate anymore, remove it
            if($violations = $validator->validate($existingBundle)->count()) {
                $output->writeLn(sprintf('Remove %s : %s', $existingBundle->getFullName(), print_r($violations)));
                ++$counters['removed'];
                $dm->remove($existingBundle);
            }
            else {
                $output->writeLn(sprintf('Update %s', $existingBundle->getFullName()));
                ++$counters['updated'];
            }
        }

        $dm->flush();
        $existingBundles = $dm->find('Application\S2bBundle\Document\Bundle')->getResults();
        
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
                $bundle = $githubBundle->import($githubRepo['username'], $githubRepo['name'], $githubRepo);
                if(!$bundle) {
                    continue;
                }
                $user = null;
                foreach($users as $u) {
                    if($githubRepo['username'] === $u->getName()) {
                        $user = $u;
                        $break;
                    }
                }
                if(!$user) {
                    $user = $githubUser->import($githubRepo['username']);
                    $users[] = $user;
                }
                $bundle->setUser($user);
                if(!$validator->validate($bundle)->count()) {
                    $user->addBundle($bundle);
                    $dm->persist($bundle);
                    $dm->persist($user);
                    ++$counters['created'];
                    $output->writeLn(sprintf('Create %s', $bundle->getName()));
                }
                else {
                    $output->writeLn(sprintf('Ignore %s', $bundle->getName()));
                }
            }
        }

        $dm->flush();
        $existingBundles = $dm->find('Application\S2bBundle\Document\Bundle')->getResults();

        $output->writeln(sprintf('%d created, %d updated, %d removed', $counters['created'], $counters['updated'], $counters['removed']));

        $output->writeln('Will now update commits, files and tags');
        // Now update bundles with more precise GitHub data
        foreach($existingBundles as $bundle) {
            $output->write($bundle->getFullName().str_repeat(' ', 50-strlen($bundle->getFullName())));
            $githubBundle->update($bundle);
            $output->writeLn(' '.$bundle->getScore());
        }
        
        $output->writeln('Will now update users');
        // Now update users with more precise GitHub data
        $users = $dm->find('Application\S2bBundle\Document\User')->getResults();
        $output->writeLn(array('', sprintf('Will now update %d users', count($users))));
        foreach($users as $user) {
            $output->write($user->getName().str_repeat(' ', 40-strlen($user->getName())));
            $githubUser->update($user);
            $output->writeLn('OK');
        }

        $dm->flush();

        $output->writeLn('Population complete.');
    }
}
