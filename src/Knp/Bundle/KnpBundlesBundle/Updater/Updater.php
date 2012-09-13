<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

use Github\HttpClient\ApiLimitExceedException;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\OwnerManager;
use Knp\Bundle\KnpBundlesBundle\Finder\FinderInterface;
use Knp\Bundle\KnpBundlesBundle\Github\Repo;

class Updater
{
    /**
     * @var Repo
     */
    private $githubRepoApi;
    /**
     * @var FinderInterface
     */
    private $finder;
    /**
     * @var array
     */
    private $bundles;
    /**
     * @var OwnerManager
     */
    private $ownerManager;
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
     * @param \Doctrine\ORM\EntityManager  $em
     * @param OwnerManager                 $ownerManager
     * @param FinderInterface              $finder
     * @param Repo                         $githubRepoApi
     */
    public function __construct(EntityManager $em, OwnerManager $ownerManager, FinderInterface $finder, Repo $githubRepoApi)
    {
        $this->em = $em;
        $this->finder = $finder;
        $this->githubRepoApi = $githubRepoApi;
        $this->ownerManager = $ownerManager;
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
        $this->output->writeln(sprintf('[%s] Loaded <comment>%d</comment> bundles from the DB', $this->currentTime(), count($this->bundles)));
    }

    public function searchNewBundles()
    {
        $this->output->writeln(sprintf('[%s] Trying to find bundle candidates', $this->currentTime()));

        $repos = $this->finder->find();
        $bundles = array();
        foreach ($repos as $repo) {
            $bundles[strtolower($repo)] = new Bundle($repo);
        }
        $this->output->writeln(sprintf('[%s] Found <comment>%d</comment> bundle candidates', $this->currentTime(), count($bundles)));

        return $bundles;
    }

    public function createMissingBundles($foundBundles)
    {
        $added = 0;

        /* @var $bundle Bundle */
        foreach ($foundBundles as $bundle) {
            // We have it in DB already, skip it
            if (isset($this->bundles[strtolower($bundle->getFullName())])) {
                continue;
            }

            $this->githubRepoApi->updateFiles($bundle, array('sf'));

            // It's not an valid Symfony2 Bundle
            if (!$bundle->isValid()) {
                $this->notifyInvalid($bundle, sprintf('Class "%sBundle" was not found.', ucfirst($bundle->getFullName())));
                continue;
            }
            // It's doesn't catch in our requirements (don't exists, or is a fork with less then 10 watchers)
            if (!$this->githubRepoApi->updateInfos($bundle)) {
                $this->notifyInvalid($bundle, 'Bundle not contain required informations, or we were not able to get such via API.');
                continue;
            }
            $this->output->write(sprintf('[%s] Discover bundle <comment>%s</comment>: ', $this->currentTime(), $bundle->getFullName()));
            $owner = $this->ownerManager->getOrCreate($bundle->getOwnerName());

            $owner->addBundle($bundle);
            $this->bundles[strtolower($bundle->getFullName())] = $bundle;

            $this->githubRepoApi->updateFiles($bundle);

            $this->em->persist($bundle);
            $this->em->flush();

            $this->updateRepo($bundle);

            $this->output->writeln(' ADDED');
            ++$added;
        }

        $this->output->writeln(sprintf('[%s] <comment>%d</comment> created', $this->currentTime(), $added));
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
        list($ownerName, $bundleName) = explode('/', $fullName);

        $owner = $this->ownerManager->getOrCreate($ownerName);

        if (!$owner) {
            return false;
        }

        if (!isset($this->bundles[strtolower($fullName)])) {
            $bundle = new Bundle($fullName);
            $bundle->setOwner($owner);
            $this->em->persist($bundle);
            $owner->addBundle($bundle);
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
        if ($this->bundleUpdateProducer) {
            // Create a Message object
            $message = array('bundle_id' => $bundle->getId());

            // RabbitMQ, publish my message!
            $this->bundleUpdateProducer->publish(json_encode($message));
        }
    }

    public function removeRepo(Bundle $bundle)
    {
        if ($this->bundleUpdateProducer) {
            // Create a Message object
            $message = array(
                'bundle_id' => $bundle->getId(),
                'action'    => 'remove'
            );

            // RabbitMQ, publish my message!
            $this->bundleUpdateProducer->publish(json_encode($message));

            return true;
        }

        return false;
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

    public function removeNonSymfonyBundles()
    {
        if (count($this->bundles) === 0) {
            $this->setUp();
        }

        $this->output->writeln(sprintf('[%s] Will now check <comment>%d</comment> bundles', $this->currentTime(), count($this->bundles)));

        $counter = 0;
        foreach ($this->bundles as $key => $bundle) {
            /** @var $bundle \Knp\Bundle\KnpBundlesBundle\Entity\Bundle */
            $this->githubRepoApi->updateFiles($bundle, array('sf'));
            if (!$bundle->isValid()) {
                if (!$this->removeRepo($bundle)) {
                    $bundle->getOwner()->removeBundle($bundle);
                    $this->em->remove($bundle);
                }

                $this->notifyInvalid($bundle, sprintf('Class "%sBundle" was not found.', ucfirst($bundle->getFullName())));

                unset($this->bundles[$key]);

                ++$counter;
            }
        }

        $this->output->writeln(sprintf('[%s] <comment>%s</comment> invalid bundles have been found and removed', $this->currentTime(), $counter));

        $this->em->flush();
    }

    private function notifyInvalid(Bundle $bundle, $reason = null)
    {
        $this->output->writeln(sprintf('[%s] <error>%s</error>: INVALID - reason: %s', $this->currentTime(), $bundle->getFullName(), $reason));
    }

    private function currentTime()
    {
        return date('d-m-y H:i:s');
    }
}
