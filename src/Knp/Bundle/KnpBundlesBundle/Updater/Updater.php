<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Github\HttpClient\ApiLimitExceedException;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Finder\FinderInterface;
use Knp\Bundle\KnpBundlesBundle\Github\Repo;
use Knp\Bundle\KnpBundlesBundle\Manager\BundleManager;

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
     * @var BundleManager
     */
    private $bundleManager;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var Producer
     */
    private $bundleUpdateProducer;

    /**
     * @param EntityManager   $em
     * @param BundleManager   $bundleManager
     * @param FinderInterface $finder
     * @param Repo            $githubRepoApi
     */
    public function __construct(EntityManager $em, BundleManager $bundleManager, FinderInterface $finder, Repo $githubRepoApi)
    {
        $this->em = $em;
        $this->finder = $finder;
        $this->githubRepoApi = $githubRepoApi;
        $this->bundleManager = $bundleManager;
        $this->output = new NullOutput();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Producer $bundleUpdateProducer
     */
    public function setBundleUpdateProducer(Producer $bundleUpdateProducer)
    {
        $this->bundleUpdateProducer = $bundleUpdateProducer;
    }

    public function searchNewBundles()
    {
        $this->output->writeln(sprintf('[%s] Trying to find bundle candidates', date('d-m-y H:i:s')));

        $bundles = array();
        foreach ($this->finder->find() as $fullName) {
            list($ownerName, $bundleName) = explode('/', $fullName);
            // We have it in DB already, skip it
            if ($this->bundleManager->findBundleBy(array('ownerName' => $ownerName, 'name' => $bundleName))) {
                continue;
            }

            $bundles[] = $fullName;
        }
        $this->output->writeln(sprintf('[%s] Found <comment>%d</comment> bundle candidates', date('d-m-y H:i:s'), count($bundles)));

        return $bundles;
    }

    public function createMissingBundles(array $foundBundles)
    {
        $added = 0;

        /* @var $bundle Bundle */
        foreach ($foundBundles as $fullName) {
            $bundle = $this->bundleManager->createBundle($fullName);

            // It's not a valid Symfony2 Bundle or failed with our requirements (i.e: is a fork with less then 10 watchers)
            if (!$bundle) {
                $this->notifyInvalid($bundle, 'Bundle is not an valid Symfony2 Bundle or failed with our requirements , or we were not able to get such via API.');
                continue;
            }
            $this->output->write(sprintf('[%s] Discover bundle <comment>%s</comment>: ', date('d-m-y H:i:s'), $bundle->getFullName()));

            try {
                $this->githubRepoApi->updateFiles($bundle);
            } catch (\RuntimeException $e) {
                $this->output->writeln(sprintf(' <error>%s</error>', $e->getMessage()));

                continue;
            }

            $this->em->persist($bundle);

            $this->updateRepo($bundle);

            $this->output->writeln(' ADDED');
            ++$added;
        }

        $this->em->flush();

        if ($added) {
            $this->output->writeln(sprintf('[%s] Created <comment>%d</comment> new bundles', date('d-m-y H:i:s'), $added));
        }
    }

    /**
     * Add or update a repo
     *
     * @param string  $fullName    A full repo name like KnpLabs/KnpMenuBundle
     * @param boolean $updateRepo  Whether or not to fetch information
     *
     * @return boolean|Bundle
     */
    public function addBundle($fullName, $updateRepo = true)
    {
        $bundle = $this->bundleManager->createBundle($fullName);
        if (!$bundle) {
            return false;
        }

        if ($updateRepo) {
            $this->updateRepo($bundle);
        }

        return $bundle;
    }

    public function updateBundlesData()
    {
        $this->output->writeln(sprintf('[%s] Will now update commits, files and tags', date('d-m-y H:i:s')));

        $unitOfWork = $this->em->getUnitOfWork();

        $page  = 1;
        $pager = $this->paginateExistingBundles($page);
        do {
            // Now update bundles with more precise GitHub data
            /** @var $bundle Bundle */
            foreach ($pager->getCurrentPageResults() as $bundle) {
                if (UnitOfWork::STATE_MANAGED !== $unitOfWork->getEntityState($bundle)) {
                    continue;
                }

                $this->updateRepo($bundle);
            }

            ++$page;
        } while ($pager->hasNextPage() && $pager->setCurrentPage($page, false, true));
    }

    public function updateBundleData($owner, $name)
    {
        $bundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findOneByOwnerNameAndName($owner, $name);
        $this->updateRepo($bundle);
    }

    public function removeNonSymfonyBundles()
    {
        $counter = 0;

        $page  = 1;
        $pager = $this->paginateExistingBundles($page);

        $this->output->writeln(sprintf('[%s] Will now check <comment>%d</comment> bundles', date('d-m-y H:i:s'), $pager->getNbResults()));
        do {
            /** @var $bundle Bundle */
            foreach ($pager->getCurrentPageResults() as $bundle) {
                if (!$this->githubRepoApi->validate($bundle)) {
                    $this->notifyInvalid($bundle->getFullName(), sprintf('File "%sBundle.php" with base class was not found.', ucfirst($bundle->getFullName())));

                    if (!$this->removeRepo($bundle)) {
                        $bundle->getOwner()->removeBundle($bundle);
                        $this->em->remove($bundle);
                    }

                    ++$counter;
                }
            }

            ++$page;
        } while ($pager->hasNextPage() && $pager->setCurrentPage($page, false, true));

        $this->output->writeln(sprintf('[%s] <comment>%s</comment> invalid bundles have been found and removed', date('d-m-y H:i:s'), $counter));

        $this->em->flush();
    }

    public function cleanupBundlesActivities($limit = 30)
    {
        $counter = 0;

        $page  = 1;
        $pager = $this->paginateExistingBundles($page);
        $activityRepository = $this->em->getRepository('KnpBundlesBundle:Activity');

        do {
            /** @var $bundle Bundle */
            foreach ($pager->getCurrentPageResults() as $bundle) {
                $countActivities = $activityRepository->countActivitiesByBundle($bundle);
                if ($countActivities > $limit) {
                    try {
                        $latestActivities = $activityRepository->findLastActivitiesForBundle($bundle, $limit);

                        $leftActivities = array();
                        foreach ($latestActivities as $activity) {
                            $leftActivities[] = $activity->getId();
                        }

                        $activityRepository->removeActivities($bundle, $leftActivities);
                        ++$counter;
                        // echoes progress dot
                        $this->output->write('<info>.</info>');
                    } catch (\Exception $e) {

                    }
                }
            }

            ++$page;
        } while ($pager->hasNextPage() && $pager->setCurrentPage($page, false, true));

        $this->output->writeln('');
        $this->output->writeln(sprintf('[%s] for <comment>%s</comment> bundles activities were cut', date('d-m-y H:i:s'), $counter));

        $this->em->flush();
    }

    /**
     * @param Bundle $bundle
     */
    public function updateRepo(Bundle $bundle)
    {
        if ($this->bundleUpdateProducer) {
            // Create a Message object
            $message = array('bundle_id' => $bundle->getId());

            // RabbitMQ, publish my message!
            $this->bundleUpdateProducer->publish(json_encode($message));
        }
    }

    /**
     * @param Bundle $bundle
     *
     * @return boolean
     */
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

    /**
     * @param string|false      $bundle
     * @param null|string $reason
     */
    private function notifyInvalid($bundle, $reason = null)
    {
        $this->output->writeln(sprintf('[%s] <error>%s</error>: INVALID - reason: %s', date('d-m-y H:i:s'), $bundle, $reason));
    }

    /**
     * @param integer $page
     * @param integer $limit
     *
     * @return Pagerfanta
     */
    private function paginateExistingBundles($page, $limit = 100)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($this->em->getRepository('KnpBundlesBundle:Bundle')->queryAllSortedBy('updatedAt'), false));
        $pager
            ->setMaxPerPage($limit)
            ->setCurrentPage($page, false, true)
        ;

        if (1 === $page) {
            $this->output->writeln(sprintf('[%s] Loaded <comment>%d</comment> bundles from the DB', date('d-m-y H:i:s'), $pager->getNbResults()));
        }

        return $pager;
    }
}
