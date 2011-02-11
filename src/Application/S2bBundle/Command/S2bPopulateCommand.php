<?php

namespace Application\S2bBundle\Command;

use Application\S2bBundle\Entity\Repo;
use Application\S2bBundle\Entity\User;
use Application\S2bBundle\Github;
use Application\S2bBundle\Git;
use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\ORM\UnitOfWork;

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
        $github->setRequest(new Github\Request());
        $githubSearch = new Github\Search($github, new \Goutte\Client(), $output);
        $githubUser = new Github\User($github, $output);
        $gitRepoDir = $this->container->getParameter('kernel.cache_dir').'/repos';
        $gitRepoManager = new Git\RepoManager($gitRepoDir);
        $githubRepo = new Github\Repo($github, $output, $gitRepoManager);

        $foundRepos = $githubSearch->searchRepos(500, $output);
        $output->writeLn(sprintf('Found %d repo candidates', count($foundRepos)));

        $dm = $this->container->getDoctrine_Orm_DefaultEntityManagerService();
        $repos = array();
        foreach($dm->getRepository('Application\S2bBundle\Entity\Repo')->findAll() as $repo) {
            $repos[$repo->getFullName()] = $repo;
        }
        $users = array();
        foreach($dm->getRepository('Application\S2bBundle\Entity\User')->findAll() as $user) {
            $users[$user->getName()] = $user;
        }
        $validator = $this->container->getValidatorService();
        $counters = array(
            'created' => 0,
            'updated' => 0,
            'removed' => 0
        );

        // create missing repos
        foreach($foundRepos as $repo) {
            if(isset($repos[$repo->getFullName()])) {
                continue;
            }
            $output->write(sprintf('Discover %s:', $repo->getFullName()));
            if(isset($users[$repo->getUsername()])) {
                $user = $users[$repo->getUsername()];
            }
            else {
                $user = $githubUser->import($repo->getUsername());
                $users[$user->getName()] = $user;
                $dm->persist($user);
            }

            $user->addRepo($repo);
            $repos[$repo->getFullName()] = $repo;
            $dm->persist($repo);
            $output->writeLn(' ADDED');
            ++$counters['created'];
        }

        $output->writeln(sprintf('%d created, %d updated, %d removed', $counters['created'], $counters['updated'], $counters['removed']));

        $output->writeln('Will now update commits, files and tags');
        // Now update repos with more precise GitHub data
        foreach($repos as $repo) {
            if($dm->getUnitOfWork()->getEntityState($repo) != UnitOfWork::STATE_MANAGED) {
                continue;
            }
            $output->write($repo->getFullName().str_repeat(' ', 50-strlen($repo->getFullName())));
            if(!$githubRepo->update($repo)) {
                $output->write(' - Fail, will be removed');
                $repo->getUser()->removeRepo($repo);
                $dm->remove($repo);
            }
            $output->writeLn(' '.$repo->getScore());
        }

        $output->writeLn('Will now update contributors');
        foreach($repos as $repo) {
            if($dm->getUnitOfWork()->getEntityState($repo) != UnitOfWork::STATE_MANAGED) {
                continue;
            }
            $contributorNames = $githubRepo->getContributorNames($repo);
            $contributors = array();
            foreach($contributorNames as $contributorName) {
                if(!isset($users[$contributorName])) {
                    $user = $githubUser->import($contributorName);
                    $users[$user->getName()] = $user;
                    $dm->persist($user);
                }
                $contributors[] = $users[$contributorName];
            }
            $output->writeLn(sprintf('%s contributors: %s', $repo->getFullName(), implode(', ', $contributors)));
            $repo->setContributors($contributors);
        }

        // Now update users with more precise GitHub data
        $output->writeLn(sprintf('Will now update %d users', count($users)));
        foreach($users as $user) {
            if($dm->getUnitOfWork()->getEntityState($user) != UnitOfWork::STATE_MANAGED) {
                continue;
            }
            $output->write($user->getName().str_repeat(' ', 40-strlen($user->getName())));
            if(!$githubUser->update($user)) {
                $output->writeLn('Remove user');
                $dm->remove($user);
            }
            else {
                $user->recalculateScore();
                $output->writeLn('OK, score is '.$user->getScore());
            }
        }

        $output->writeLn('Will now flush changes to the database');
        $dm->flush();

        $output->writeLn('Population complete.');
    }
}
