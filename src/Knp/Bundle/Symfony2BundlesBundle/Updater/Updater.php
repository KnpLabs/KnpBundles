<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Updater;

use Knp\Bundle\Symfony2BundlesBundle\Github;
use Knp\Bundle\Symfony2BundlesBundle\Git;
use Doctrine\ORM\UnitOfWork;

class Updater
{
    private $githubClient;
    private $githubUserApi;
    private $githubRepoApi;
    private $githubSearch;
    private $gitRepoManager;
    private $repos;
    private $users;
    private $em;
    private $output;

    public function __construct($em, $gitRepoDir, $gitBin, $output)
    {
        $this->output = $output;
        $this->em = $em;
        $this->githubClient = new \Github_Client();
        $this->githubSearch = new Github\Search($this->githubClient, new \Goutte\Client(), $this->output);
        $this->githubUserApi = new Github\User($this->githubClient, $this->output);

        $this->gitRepoManager = new Git\RepoManager($gitRepoDir, $gitBin);
        $this->githubRepoApi = new Github\Repo($this->githubClient, $this->output, $this->gitRepoManager);
    }

    public function setUp()
    {
        $this->repos = array();
        foreach ($this->em->createQuery('SELECT r FROM KnpSymfony2BundlesBundle:Repo r ORDER BY r.updatedAt DESC')->execute() as $repo) {
            $this->repos[strtolower($repo->getFullName())] = $repo;
        }
        $this->output->writeln(sprintf('Loaded %d repos from the DB', count($this->repos)));

        $this->users = array();
        foreach ($this->em->getRepository('Knp\Bundle\Symfony2BundlesBundle\Entity\User')->findAll() as $user) {
            $this->users[strtolower($user->getName())] = $user;
        }
        $this->output->writeln(sprintf('Loaded %d users from the DB', count($this->users)));
    }

    public function searchNewRepos($nb)
    {
        $foundRepos = $this->githubSearch->searchRepos($nb, $this->output);
        $this->output->writeln(sprintf('Found %d repo candidates', count($foundRepos)));

        return $foundRepos;
    }

    public function createMissingRepos($foundRepos)
    {
        $added = 0;

        foreach ($foundRepos as $repo) {
            if (isset($this->repos[strtolower($repo->getFullName())])) {
                continue;
            }
            $this->output->write(sprintf('Discover repo %s: ', $repo->getFullName()));
            $user = $this->getOrCreateUser($repo->getUsername());

            $user->addRepo($repo);
            $this->repos[strtolower($repo->getFullName())] = $repo;
            $this->em->persist($repo);
            $this->output->writeln(' ADDED');
            ++$added;
        }

        $this->output->writeln(sprintf('%d created', $added));
    }

    public function updateReposData()
    {
        $this->output->writeln('Will now update commits, files and tags');
        // Now update repos with more precise GitHub data
        $now = time();
        foreach (array_reverse($this->repos) as $repo) {
            if ($this->em->getUnitOfWork()->getEntityState($repo) != UnitOfWork::STATE_MANAGED) {
                continue;
            }

            $lastUpdateHappend = $now - $repo->getUpdatedAt()->getTimestamp();

            if ($lastUpdateHappend < 60*60*3 && count($repo->getLastCommits()) > 0) {
                continue;
            }

            $this->output->writeln("Repo $repo has score ".$repo->getScore());

            $this->output->write($repo->getFullName());
            $pad = 50 - strlen($repo->getFullName());
            if ($pad > 0) {
                $this->output->write(str_repeat(' ', $pad));
            }
            if (!$this->githubRepoApi->update($repo)) {
                $this->output->write(' - Fail, will be removed');
                $repo->getUser()->removeRepo($repo);
                $this->em->remove($repo);
            }
            $this->output->writeln(' '.$repo->getScore());
            $this->em->flush();
            sleep(1);

            $contributorNames = $this->githubRepoApi->getContributorNames($repo);
            $contributors = array();
            foreach ($contributorNames as $contributorName) {
                $contributors[] = $this->getOrCreateUser($contributorName);
            }
            $this->output->writeln(sprintf('%s contributors: %s', $repo->getFullName(), implode(', ', $contributors)));
            $repo->setContributors($contributors);
            $this->em->flush();
            sleep(1);
        }
    }

    public function updateUsers()
    {
        $this->output->writeln(sprintf('Will now update %d users', count($this->users)));
        foreach ($this->users as $user) {
            if ($this->em->getUnitOfWork()->getEntityState($user) != UnitOfWork::STATE_MANAGED) {
                continue;
            }

            $this->output->write($user->getName().str_repeat(' ', 40-strlen($user->getName())));
            if (!$this->githubUserApi->update($user)) {
                $this->output->writeln('Remove user');
                $this->em->remove($user);
            } else {
                $user->recalculateScore();
                $this->output->writeln('OK, score is '.$user->getScore());
            }
        }
    }

    private function getOrCreateUser($username)
    {
        if (isset($this->users[strtolower($username)])) {
            $user = $this->users[strtolower($username)];
        } else {
            $this->output->write(sprintf('Add user %s:', $username));
            $user = $this->githubUserApi->import($username);
            $this->users[strtolower($user->getName())] = $user;
            $this->em->persist($user);
        }

        return $user;
    }
}
