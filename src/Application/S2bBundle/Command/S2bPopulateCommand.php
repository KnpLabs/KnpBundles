<?php

namespace Application\S2bBundle\Command;

use Application\S2bBundle\Entities\Bundle;
use Application\S2bBundle\Entities\User;
use Application\S2bBundle\Github;
use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
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

        $foundBundles = $githubSearch->searchBundles(5000, $output);
        $output->writeLn(sprintf('Found %d bundle candidates', count($foundBundles)));

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
            $githubBundle->updateInfos($existingBundle);
            if(!$existingBundle->getIsOnGithub()) {
                $output->writeLn(sprintf('Remove %s : no more on Github', $existingBundle->getFullName()));
                ++$counters['removed'];
                $existingBundle->getUser()->removeBundle($existingBundle);
                $dm->remove($existingBundle);
                continue;
            }

            // if the bundles doesnt validate anymore, remove it
            if(count($violations = $validator->validate($existingBundle))) {
                $output->writeLn(sprintf('Remove %s : %s', $existingBundle->getFullName(), $violations->__toString()));
                ++$counters['removed'];
                $existingBundle->getUser()->removeBundle($existingBundle);
                $dm->remove($existingBundle);
                continue;
            }

            $output->writeLn(sprintf('Update %s', $existingBundle->getFullName()));
            ++$counters['updated'];
        }

        $dm->flush();
        $existingBundles = $dm->find('Application\S2bBundle\Document\Bundle')->getResults();
        
        // second pass, create missing bundles
        foreach($foundBundles as $foundBundle) {
            $exists = false;
            foreach($existingBundles as $existingBundle) {
                if($existingBundle->getFullName() === $foundBundle->getFullName()) {
                    $exists = true;
                    break;
                }
            }
            if($exists) {
                continue;
            }

            if(!$bundle = $githubBundle->updateInfos($foundBundle)) {
                continue;
            }
            $user = null;
            foreach($users as $u) {
                if($bundle->getUsername() === $u->getName()) {
                    $user = $u;
                    $break;
                }
            }
            if(!$user) {
                $user = $githubUser->import($bundle->getUsername());
                $users[] = $user;
            }
            $bundle->setUser($user);
            if(!count($violations = $validator->validate($bundle))) {
                $user->addBundle($bundle);
                $dm->persist($bundle);
                $dm->persist($user);
                ++$counters['created'];
                $output->writeLn(sprintf('Create %s', $bundle->getName()));
            }
            else {
                $output->writeLn(sprintf('Ignore %s : %s', $bundle->getFullName(), $violations->__toString()));
            }
        }

        $dm->flush();
        $existingBundles = $dm->find('Application\S2bBundle\Document\Bundle')->getResults();

        $output->writeln(sprintf('%d created, %d updated, %d removed', $counters['created'], $counters['updated'], $counters['removed']));

        $output->writeln('Will now update commits, files and tags');
        // Now update bundles with more precise GitHub data
        foreach($existingBundles as $bundle) {
            $output->write($bundle->getFullName().str_repeat(' ', 50-strlen($bundle->getFullName())));
            if(!$githubBundle->update($bundle)) {
                $output->write('Fail, will be removed');
                $bundle->getUser()->removeBundle($bundle);
                $dm->remove($bundle);
            }
            $output->writeLn(' '.$bundle->getScore());
        }
        
        $output->writeln('Will now update users');
        // Now update users with more precise GitHub data
        $users = $dm->find('Application\S2bBundle\Document\User')->getResults();
        $output->writeLn(array('', sprintf('Will now update %d users', count($users))));
        foreach($users as $user) {
            $output->write($user->getName().str_repeat(' ', 40-strlen($user->getName())));
            if(!$user->getNbBundles() || !$githubUser->update($user)) {
                $output->writeLn('No bundle, remove user');
                $dm->remove($user);
            }
            else {
                $output->writeLn('OK');
            }
        }

        $dm->flush();

        $output->writeLn('Population complete.');
    }
}
