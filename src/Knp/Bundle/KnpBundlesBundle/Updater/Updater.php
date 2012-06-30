<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

use Github\HttpClient\Exception as GithubException;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\UserManager;
use Knp\Bundle\KnpBundlesBundle\Finder\FinderInterface;
use Knp\Bundle\KnpBundlesBundle\Github\User;
use Knp\Bundle\KnpBundlesBundle\Github\Repo;
use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;

class Updater
{
    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Github\User
     */
    private $githubUserApi;
    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Github\Repo
     */
    private $githubRepoApi;
    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Finder\FinderInterface|
     */
    private $finder;
    /**
     * @var array
     */
    private $bundles;
    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Entity\UserManager
     */
    private $users;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    /**
     * @var \Symfony\Component\Console\Output\NullOutput
     */
    private $output;
    /**
     * @var \OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    private $bundleUpdateProducer;

    /**
     * @param \Doctrine\ORM\EntityManager                              $em
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\UserManager          $users
     * @param \Knp\Bundle\KnpBundlesBundle\Finder\FinderInterface      $finder
     * @param \Knp\Bundle\KnpBundlesBundle\Github\User                 $githubUserApi
     */
    public function __construct(EntityManager $em, UserManager $users, FinderInterface $finder, User $githubUserApi, Repo $githubRepoApi)
    {
        $this->em = $em;
        $this->finder = $finder;
        $this->githubUserApi = $githubUserApi;
        $this->githubRepoApi = $githubRepoApi;
        $this->users = $users;
        $this->output = new NullOutput();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setBundleUpdateProducer(Producer $bundleUpdateProducer)
    {
        $this->bundleUpdateProducer = $bundleUpdateProducer;
    }

    public function setUp()
    {
        $this->bundles = array();
        foreach ($this->em->getRepository('KnpBundlesBundle:Bundle')->findAllSortedBy('updatedAt') as $bundle) {
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;
        }
        $this->output->writeln(sprintf('[%s] Loaded %d bundles from the DB', $this->currentTime(), count($this->bundles)));
    }

    public function searchNewBundles()
    {
        $this->output->writeln(sprintf('[%s] Trying to find bundle candidates'), $this->currentTime());

        $repos = $this->finder->find();
        $bundles = array();
        foreach ($repos as $repo) {
            $bundles[strtolower($repo)] = new Bundle($repo);
        }
        $this->output->writeln(sprintf('[%s] Found %d bundle candidates', $this->currentTime(), count($bundles)));

        return $bundles;
    }

    public function createMissingBundles($foundBundles)
    {
        $added = 0;

        foreach ($foundBundles as $bundle) {
            if (isset($this->bundles[strtolower($bundle->getFullName())])) {
                continue;
            }
            if (!$this->githubRepoApi->isValidSymfonyBundle($bundle)) {
                $this->notifyInvalidBundle($bundle);
                continue;
            }
            $this->output->write(sprintf('[%s] Discover bundle %s: ', $this->currentTime(), $bundle->getFullName()));
            $user = $this->users->getOrCreate($bundle->getUsername());

            $user->addBundle($bundle);
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;
            $this->em->persist($bundle);
            $this->em->flush();

            $this->updateRepo($bundle);

            $this->output->writeln(' ADDED');
            ++$added;
        }

        $this->output->writeln(sprintf('[%s] %d created', $this->currentTime(), $added));
    }

    /**
     * Add or update a repo
     *
     * @param $fullName   string A full repo name like knplabs/KnpMenuBundle
     * @param $updateRepo boolean Wether or not to fetch informations
     * @return Bundle
     */
    public function addBundle($fullName, $updateRepo = true)
    {
        list($username, $bundleName) = explode('/', $fullName);

        $user = $this->users->getOrCreate($username);

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

    public function updateRepo(Bundle $bundle)
    {
        // Create a Message object
        $message = array('bundle_id' => $bundle->getId());

        if ($this->bundleUpdateProducer) {
            // RabbitMQ, publish my message!
            $this->bundleUpdateProducer->publish(json_encode($message));
        }
    }

    public function updateBundlesData()
    {
        $this->output->writeln(sprintf('[%s] Will now update commits, files and tags', $this->currentTime()));
        // Now update repos with more precise GitHub data
        foreach (array_reverse($this->bundles) as $bundle) {
            if ($this->em->getUnitOfWork()->getEntityState($bundle) != UnitOfWork::STATE_MANAGED) {
                continue;
            }
            $this->updateRepo($bundle);
        }
    }

    public function updateUsers()
    {
        $this->output->writeln(sprintf('[%s] Will now update %d users', $this->currentTime(), count($this->users)));
        foreach ($this->users as $user) {
            if ($this->em->getUnitOfWork()->getEntityState($user) != UnitOfWork::STATE_MANAGED) {
                continue;
            }

            while (true) {
                try {
                    $this->output->write($user->getName() . str_repeat(' ', 40 - strlen($user->getName())));
                    if (!$this->githubUserApi->update($user)) {
                        $this->output->writeln(sprintf('[%s] Remove user', $this->currentTime()));
                        $this->em->remove($user);
                    } else {
                        $user->recalculateScore();
                        $this->output->writeln(sprintf('[%s] OK, score is %s', $this->currentTime(), $user->getScore()));
                    }
                    break;
                } catch (GithubException $e) {
                    $this->output->writeln("Got a Github exception, sleeping for a few secs before trying again");
                    sleep(60);
                }
            }
        }
    }

    public function removeNonSymfonyBundles()
    {
        if (count($this->bundles) === 0) {
            $this->setUp();
        }

        $counter = 0;
        foreach ($this->bundles as $key => $bundle) {
            /** @var $bundle \Knp\Bundle\KnpBundlesBundle\Entity\Bundle */
            if (false === $this->githubRepoApi->isValidSymfonyBundle($bundle)) {
                $this->notifyInvalidBundle($bundle);
                $bundle->getUser()->removeBundle($bundle);
                $this->em->remove($bundle);
                $counter++;
            }
        }

        $this->output->writeln(sprintf('%s invalid bundles have been found and removed', $counter));

        $this->em->flush();
    }

    private function notifyInvalidBundle(Bundle $bundle)
    {
        $this->output->writeln(sprintf("[%s] %s: invalid Symfony bundle", $this->currentTime(), $bundle->getFullName()));
    }

    private function currentTime()
    {
        return date('D, d M Y H:i:s');
    }
}
