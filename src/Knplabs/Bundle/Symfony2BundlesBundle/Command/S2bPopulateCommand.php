<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Command;

use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Repo;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\User;
use Knplabs\Bundle\Symfony2BundlesBundle\Github;
use Knplabs\Bundle\Symfony2BundlesBundle\Git;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Doctrine\ORM\UnitOfWork;

/**
 * Update local database from web searches
 */
class S2bPopulateCommand extends ContainerAwareCommand
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
        $github = new \Github_Client();
        $githubSearch = new Github\Search($github, new \Goutte\Client(), $output);
        $githubUser = new Github\User($github, $output);
        $gitRepoDir = $this->getContainer()->getParameter('kernel.cache_dir').'/repos';
        $gitRepoManager = new Git\RepoManager($gitRepoDir, false, array('gitExecutable' => $this->getContainer()->getParameter('symfony2bundles.git_bin')));
        $githubRepo = new Github\Repo($github, $output, $gitRepoManager);

        $foundRepos = $githubSearch->searchRepos(500, $output);
        $output->writeLn(sprintf('Found %d repo candidates', count($foundRepos)));

        $dm = $this->getContainer()->get('symfony2bundles.entity_manager');
        $repos = array();
        foreach($dm->getRepository('Knplabs\Bundle\Symfony2BundlesBundle\Entity\Repo')->findAll() as $repo) {
            $repos[strtolower($repo->getFullName())] = $repo;
        }
        $users = array();
        foreach($dm->getRepository('Knplabs\Bundle\Symfony2BundlesBundle\Entity\User')->findAll() as $user) {
            $users[strtolower($user->getName())] = $user;
        }
        $counters = array(
            'created' => 0,
            'updated' => 0,
            'removed' => 0
        );

        // create missing repos
        foreach($foundRepos as $repo) {
            if(isset($repos[strtolower($repo->getFullName())])) {
                continue;
            }
            $output->write(sprintf('Discover %s:', $repo->getFullName()));
            if(isset($users[strtolower($repo->getUsername())])) {
                $user = $users[strtolower($repo->getUsername())];
            }
            else {
                $user = $githubUser->import($repo->getUsername());
                $users[strtolower($user->getName())] = $user;
                $dm->persist($user);
            }

            $user->addRepo($repo);
            $repos[strtolower($repo->getFullName())] = $repo;
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
            $output->write($repo->getFullName());
            $pad = 50 - strlen($repo->getFullName());
            if ($pad > 0) {
                $output->write(str_repeat(' ', $pad));
            }
            if(!$githubRepo->update($repo)) {
                $output->write(' - Fail, will be removed');
                $repo->getUser()->removeRepo($repo);
                $dm->remove($repo);
            }
            $output->writeLn(' '.$repo->getScore());
        }
        $dm->flush();

        $output->writeLn('Will now update contributors');
        foreach($repos as $repo) {
            if($dm->getUnitOfWork()->getEntityState($repo) != UnitOfWork::STATE_MANAGED) {
                continue;
            }
            $contributorNames = $githubRepo->getContributorNames($repo);
            $contributors = array();
            foreach($contributorNames as $contributorName) {
                if(!isset($users[strtolower($contributorName)])) {
                    $user = $githubUser->import($contributorName);
                    $users[strtolower($user->getName())] = $user;
                    $dm->persist($user);
                }
                $contributors[] = $users[strtolower($contributorName)];
            }
            $output->writeLn(sprintf('%s contributors: %s', $repo->getFullName(), implode(', ', $contributors)));
            $repo->setContributors($contributors);
        }
        $dm->flush();

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
