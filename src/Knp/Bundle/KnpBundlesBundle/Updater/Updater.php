<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Knp\Bundle\KnpBundlesBundle\Github;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;
use Doctrine\ORM\UnitOfWork;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class Updater
{
    private $githubClient;
    private $githubUserApi;
    private $githubRepoApi;
    private $githubSearch;
    private $gitRepoManager;
    private $travis;
    private $bundles;
    private $users;
    private $em;
    private $output;

    public function __construct(EntityManager $em, $gitRepoDir, $gitBin, Producer $bundleUpdateProducer, OutputInterface $output = null)
    {
        $this->output = $output ?: new NullOutput();
        $this->em = $em;
        $this->githubClient = new \Github_Client();
        $this->githubSearch = new Github\Search($this->githubClient, new \Goutte\Client(), $this->output);
        $this->githubUserApi = new Github\User($this->githubClient, $this->output);

        $this->gitRepoManager = new Git\RepoManager($gitRepoDir, $gitBin);
        $this->githubRepoApi = new Github\Repo($this->githubClient, $this->output, $this->gitRepoManager);
        $this->travis = new Travis($this->output);
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setUp()
    {
        $this->bundles = array();
        foreach ($this->em->createQuery('SELECT b FROM KnpBundlesBundle:Bundle b ORDER BY b.updatedAt DESC')->execute() as $bundle) {
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;
        }
        $this->output->writeln(sprintf('Loaded %d bundles from the DB', count($this->bundles)));

        $this->users = array();
        foreach ($this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\User')->findAll() as $user) {
            $this->users[strtolower($user->getName())] = $user;
        }
        $this->output->writeln(sprintf('Loaded %d users from the DB', count($this->users)));
    }

    public function searchNewBundles($nb)
    {
        $foundBundles = $this->githubSearch->searchBundles($nb, $this->output);
        $this->output->writeln(sprintf('Found %d bundle candidates', count($foundBundles)));

        return $foundBundles;
    }

    public function createMissingBundles($foundBundles)
    {
        $added = 0;

        foreach ($foundBundles as $bundle) {
            if (isset($this->bundles[strtolower($bundle->getFullName())])) {
                continue;
            }
            $this->output->write(sprintf('Discover bundle %s: ', $bundle->getFullName()));
            $user = $this->getOrCreateUser($bundle->getUsername());

            $user->addBundle($bundle);
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;
            $this->em->persist($bundle);
            $this->output->writeln(' ADDED');
            ++$added;
        }

        $this->output->writeln(sprintf('%d created', $added));
    }
    
    /**
     * Add or update a repo
     *
     * @param string A full repo name like knplabs/KnpMenuBundle
     * @param boolean Wether or not to fetch informations
     * @return Bundle
     */
    public function addBundle($fullName, $updateRepo = true)
    {
        list($username, $bundleName) = explode('/', $fullName);
        
        if (!isset($this->users[strtolower($username)])) {
            $user = $this->getOrCreateUser($username);
            $this->users[strtolower($username)] = $user;
        } else {
            $user = $this->users[strtolower($username)];
        }

        if (!isset($this->bundles[strtolower($fullName)])) {
            $bundle = new Bundle($fullName);
            $this->em->persist($bundle);
            $user->addBundle($bundle);
            $this->bundles[strtolower($fullName)] = $bundle;
        } else {
            $bundle = $this->bundles[strtolower($fullName)];
        }

        $this->em->flush();
        
        if ($updateRepo) {
            $this->updateRepo($bundle);
        }

        return $bundle;
    }

    public function updateBundlesData()
    {
        $this->output->writeln('Will now update commits, files and tags');
        // Now update repos with more precise GitHub data
        foreach (array_reverse($this->bundles) as $bundle) {
            if ($this->em->getUnitOfWork()->getEntityState($bundle) != UnitOfWork::STATE_MANAGED) {
                continue;
            }

            while (true) {
                $this->output->writeln("\n\n#################### Updating ".$bundle);
                try {
                    $this->updateRepo($bundle);
                    break;
                } catch(\Github_HttpClient_Exception $e) {
                    $this->output->writeln("Got a Github exception $e, sleeping for a few secs before trying again");
                    sleep(60);
                }
            }
        }
    }
    
    public function updateRepo(Bundle $bundle)
    {
        $this->output->write($bundle->getFullName());
        $pad = 50 - strlen($bundle->getFullName());
        if ($pad > 0) {
            $this->output->write(str_repeat(' ', $pad));
        }
        if (!$this->githubRepoApi->update($bundle)) {
            $this->output->write(' - Fail, skipping, will be removed if date of last successful check is older than 3 days.');

            if (null !== ($lastCheck = $bundle->getLastCheckAt())) {
                $now = new \DateTime();
                if ($now->diff($lastCheck)->days > 3) {
                    $bundle->getUser()->removeBundle($bundle);

                    $this->em->remove($bundle);
                    $this->em->flush();
                }
            }

            return false;
        } else {
            // Success so we set last check date
            $bundle->setLastCheckAt(new \DateTime());

            $score = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Score')->setScore(new \DateTime(), $bundle, $bundle->getScore());
            $this->em->persist($score);
        }
        $this->output->writeln(' '.$bundle->getScore());
        $this->em->flush();

        $contributorNames = $this->githubRepoApi->getContributorNames($bundle);
        $contributors = array();
        foreach ($contributorNames as $contributorName) {
            $contributors[] = $this->getOrCreateUser($contributorName);
        }
        $this->output->writeln(sprintf('%s contributors: %s', $bundle->getFullName(), implode(', ', $contributors)));
        $bundle->setContributors($contributors);
        $this->em->flush();
        
        if ($bundle->getUsesTravisCi()) {
            $this->travis->update($bundle);
        }
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

    public function getOrCreateUser($username)
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
