<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Knp\Bundle\KnpBundlesBundle\Github\Search;
use Knp\Bundle\KnpBundlesBundle\Github\User;
use Doctrine\ORM\UnitOfWork;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManager;
use Knp\Bundle\KnpBundlesBundle\Entity\UserManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class Updater
{
    private $githubUserApi;
    private $githubSearch;
    private $bundles;
    private $users;
    private $em;
    private $output;
    private $bundleUpdateProducer;

    public function __construct(EntityManager $em,  UserManager $users, Search $githubSearch, User $githubUserApi)
    {
        $this->em = $em;
        $this->githubSearch = $githubSearch;
        $this->githubUserApi = $githubUserApi;
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
        foreach ($this->em->createQuery('SELECT b FROM KnpBundlesBundle:Bundle b ORDER BY b.updatedAt DESC')->execute() as $bundle) {
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;
        }
        $this->output->writeln(sprintf('Loaded %d bundles from the DB', count($this->bundles)));
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
            $user = $this->users->getOrCreate($bundle->getUsername());

            $user->addBundle($bundle);
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;
            $this->em->persist($bundle);
            $this->em->flush();

            $this->updateRepo($bundle);

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
        $this->output->writeln('Will now update commits, files and tags');
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
}
