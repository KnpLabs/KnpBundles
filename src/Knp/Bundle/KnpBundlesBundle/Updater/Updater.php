<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Knp\Bundle\KnpBundlesBundle\Github;
use Knp\Bundle\KnpBundlesBundle\Git;
use Doctrine\ORM\UnitOfWork;
use Knp\Bundle\KnpBundlesBundle\Entity\Repo as RepoEntity;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;

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

    public function __construct($em, $gitRepoDir, $gitBin, OutputInterface $output = null)
    {
        $this->output = $output ?: new NullOutput();
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
        foreach ($this->em->createQuery('SELECT r FROM KnpBundlesBundle:Repo r ORDER BY r.updatedAt DESC')->execute() as $repo) {
            $this->repos[strtolower($repo->getFullName())] = $repo;
        }
        $this->output->writeln(sprintf('Loaded %d repos from the DB', count($this->repos)));

        $this->users = array();
        foreach ($this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\User')->findAll() as $user) {
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
    
    /**
     * Add or update a repo
     *
     * @param string A full repo name like knplabs/KnpMenuBundle
     * @return Repo
     */
    public function addRepo($fullName, $updateRepo = true)
    {
        list($username, $repoName) = explode('/', $fullName);
        
        if (!isset($this->users[strtolower($username)])) {
            $user = $this->getOrCreateUser($username);
            $this->users[strtolower($username)] = $user;
        } else {
            $user = $this->users[strtolower($username)];
        }

        if (!isset($this->repos[strtolower($fullName)])) {
            $repo = RepoEntity::create($fullName);
            $this->em->persist($repo);
            $user->addRepo($repo);
            $this->repos[strtolower($fullName)] = $repo;
        } else {
            $repo = $this->repos[strtolower($fullName)];
        }

        $this->em->flush();
        
        if ($updateRepo) {
            $this->updateRepo($repo);
        }

        return $repo;
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

            $lastUpdateHappened = $now - $repo->getUpdatedAt()->getTimestamp();

            if ($lastUpdateHappened < 60*60*3 && count($repo->getLastCommits()) > 0) {
                continue;
            }
            
            while (true) {
                $this->output->writeln("\n\n#################### Updating ".$repo);
                try {
                    $this->updateRepo($repo);
                    break;
                } catch(\Github_HttpClient_Exception $e) {
                    $this->output->writeln("Got a Github exception $e, sleeping for a few secs before trying again");
                    sleep(60);
                }
            }
        }
    }
    
    public function updateRepo(RepoEntity $repo)
    {
        $this->output->write($repo->getFullName());
        $pad = 50 - strlen($repo->getFullName());
        if ($pad > 0) {
            $this->output->write(str_repeat(' ', $pad));
        }
        if (!$this->githubRepoApi->update($repo)) {
            $this->output->write(' - Fail, will be removed');
            $repo->getUser()->removeRepo($repo);
            $this->em->remove($repo);
            $this->em->flush();
            return false;
        }
        $this->output->writeln(' '.$repo->getScore());
        $this->em->flush();

        $contributorNames = $this->githubRepoApi->getContributorNames($repo);
        $contributors = array();
        foreach ($contributorNames as $contributorName) {
            $contributors[] = $this->getOrCreateUser($contributorName);
        }
        $this->output->writeln(sprintf('%s contributors: %s', $repo->getFullName(), implode(', ', $contributors)));
        $repo->setContributors($contributors);
        $this->em->flush();
    }

    public function updateUsers()
    {
        $this->output->writeln(sprintf('Will now update %d users', count($this->users)));
        foreach ($this->users as $user) {
            if ($this->em->getUnitOfWork()->getEntityState($user) != UnitOfWork::STATE_MANAGED) {
                continue;
            }

            while (true) {
                try {
                    $this->output->write($user->getName().str_repeat(' ', 40-strlen($user->getName())));
                    if (!$this->githubUserApi->update($user)) {
                        $this->output->writeln('Remove user');
                        $this->em->remove($user);
                    } else {
                        $user->recalculateScore();
                        $this->output->writeln('OK, score is '.$user->getScore());
                    }
                    break;
                } catch(\Github_HttpClient_Exception $e) {
                    $this->output->writeln("Got a Github exception, sleeping for a few secs before trying again");
                    sleep(60);
                }
            }
            
        }
    }

    private function getOrCreateUser($username)
    {
        if (isset($this->users[strtolower($username)])) {
            $user = $this->users[strtolower($username)];
        } else {
            $this->output->write(sprintf('Add user %s:', $username));
            if (!$user = $this->githubUserApi->import($username)) {
                throw new UserNotFoundException();
            }
            $this->users[strtolower($user->getName())] = $user;
            $this->em->persist($user);
        }

        return $user;
    }
}
