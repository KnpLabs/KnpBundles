<?php

namespace Application\S2bBundle\Command;

use Application\S2bBundle\Entities\Repo;
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
        $githubRepo = new Github\Repo($github, $output);

        $foundRepos = $githubSearch->searchRepos(200, $output);
        $output->writeLn(sprintf('Found %d repo candidates', count($foundRepos)));

        $dm = $this->container->getDoctrine_Orm_DefaultEntityManagerService();
        $existingRepos = $dm->getRepository('Application\S2bBundle\Entities\Repo')->findAll();
        $users = $dm->getRepository('Application\S2bBundle\Entities\User')->findAll();
        $validator = $this->container->getValidatorService();
        $repos = array();
        $counters = array(
            'created' => 0,
            'updated' => 0,
            'removed' => 0
        );

        //// first pass, update and revalidate existing repos
        foreach($existingRepos as $existingRepo) {
            $githubRepo->updateInfos($existingRepo);
            if(!$existingRepo->getIsOnGithub()) {
                $output->writeLn(sprintf('Remove %s : no more on Github', $existingRepo->getFullName()));
                ++$counters['removed'];
                $existingRepo->getUser()->removeRepo($existingRepo);
                $dm->remove($existingRepo);
                continue;
            }

            // if the repos doesnt validate anymore, remove it
            if(count($violations = $validator->validate($existingRepo))) {
                $output->writeLn(sprintf('Remove %s : %s', $existingRepo->getFullName(), $violations->__toString()));
                ++$counters['removed'];
                $existingRepo->getUser()->removeRepo($existingRepo);
                $dm->remove($existingRepo);
                continue;
            }

            $output->writeLn(sprintf('Update %s', $existingRepo->getFullName()));
            ++$counters['updated'];
        }

        $dm->flush();
        $existingRepos = $dm->getRepository('Application\S2bBundle\Entities\Repo')->findAll();
        
        // second pass, create missing repos
        foreach($foundRepos as $foundRepo) {
            $exists = false;
            foreach($existingRepos as $existingRepo) {
                if($existingRepo->getFullName() === $foundRepo->getFullName()) {
                    $exists = true;
                    break;
                }
            }
            if($exists) {
                continue;
            }
            $output->write(sprintf('Discover %s:', $foundRepo->getName()));
            if(!$repo = $githubRepo->update($foundRepo)) {
                $output->writeLn(' IGNORED');
                continue;
            }
            if(!$repo = $githubRepo->updateInfos($foundRepo)) {
                $output->writeLn(' IGNORED');
                continue;
            }
            $user = null;
            foreach($users as $u) {
                if($repo->getUsername() === $u->getName()) {
                    $user = $u;
                    $break;
                }
            }
            if(!$user) {
                $user = $githubUser->import($repo->getUsername());
                $users[] = $user;
            }
            $repo->setUser($user);
            if(!count($violations = $validator->validate($repo))) {
                $user->addRepo($repo);
                $dm->persist($repo);
                $dm->persist($user);
                $output->writeLn(' ADDED');
                ++$counters['created'];
            }
            else {
                $output->writeLn(sprintf('Ignore %s : %s', $repo->getFullName(), $violations->__toString()));
            }
        }

        $dm->flush();
        $existingRepos = $dm->getRepository('Application\S2bBundle\Entities\Repo')->findAll();

        $output->writeln(sprintf('%d created, %d updated, %d removed', $counters['created'], $counters['updated'], $counters['removed']));

        $output->writeln('Will now update commits, files and tags');
        // Now update repos with more precise GitHub data
        foreach($existingRepos as $repo) {
            $output->write($repo->getFullName().str_repeat(' ', 50-strlen($repo->getFullName())));
            if(!$githubRepo->update($repo)) {
                $output->write('Fail, will be removed');
                $repo->getUser()->removeRepo($repo);
                $dm->remove($repo);
            }
            $output->writeLn(' '.$repo->getScore());
        }
        
        $output->writeln('Will now update users');
        // Now update users with more precise GitHub data
        $users = $dm->getRepository('Application\S2bBundle\Entities\User')->findAll();
        $output->writeLn(array('', sprintf('Will now update %d users', count($users))));
        foreach($users as $user) {
            $output->write($user->getName().str_repeat(' ', 40-strlen($user->getName())));
            if(!$user->getNbRepos() || !$githubUser->update($user)) {
                $output->writeLn('No repo, remove user');
                $dm->remove($user);
            }
            else {
                $output->writeLn('OK');
            }
        }

        $dm->flush();

        $output->writeLn('Will now update contributors');
        $repos = $dm->getRepository('Application\S2bBundle\Entities\Repo')->findAll();
        $userRepo = $dm->getRepository('Application\S2bBundle\Entities\User');
        foreach($repos as $repo) {
            $contributorNames = $githubRepo->getContributorNames($repo);
            $contributors = array();
            foreach($contributorNames as $contributorName) {
                if($contributor = $userRepo->findOneByName($contributorName)) {
                    $contributors[] = $contributor;
                }
            }
            $output->writeLn(sprintf('%s contributors: %s', $repo->getFullName(), implode(', ', $contributors)));
            $repo->setContributors($contributors);
        }

        $dm->flush();

        $output->writeLn('Population complete.');
    }
}
