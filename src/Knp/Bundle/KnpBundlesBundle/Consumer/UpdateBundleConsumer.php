<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use Knp\Bundle\KnpBundlesBundle\Github;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\UserManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\Common\Persistence\ObjectManager;
/**
 * This class is a consumer which will retrieve a bundle from database
 * and update everything that needs to be updated.
 *
 * @author Romain Pouclet <romain.pouclet@knplabs.com>
 */
class UpdateBundleConsumer implements ConsumerInterface
{
    /**
     * @var Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Knp\Bundle\KnpBundlesBundle\Entity\UserManager
     */
    private $users;

    /**
     * @param Doctrine\Common\Persistence\ObjectManager      $em
     * @param Knp\Bundle\KnpBundlesBundle\Entity\UserManager $users
     * @param string                                         $gitRepoDir
     * @param string                                         $gitBin
     */
    public function __construct(ObjectManager $em, UserManager $users, $gitRepoDir, $gitBin)
    {
        $output = new NullOutput();

        $this->em = $em;

        $githubClient = new \Github_Client();
        $gitRepoManager = new Git\RepoManager($gitRepoDir, $gitBin);
        $this->githubRepoApi = new Github\Repo($githubClient, $output, $gitRepoManager);
        $this->travis = new Travis($output);

        $this->users = $users;
    }

    /** 
     * Only here because ConsumerInterface extends ContainerAwareInterface
     * @todo remove it once this PR is merged : https://github.com/videlalvaro/RabbitMqBundle/pull/13 
     */
    public function setContainer(ContainerInterface $container = null)
    {
    }

    /**
     * Set a logger instance
     *
     * @param Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Callback called from RabbitMQ to update a bundle
     *
     * @param string $msg serialized Message
     */
    public function execute($msg)
    {
        // Retrieve informations from the message
        $message = unserialize($msg);

        if (!isset($message['bundle_id'])) {
            if ($this->logger) {
                $this->logger->err('Bundle id is missing : skip message');
            }

            return;
        }

        $bundles = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        // Retrieve Bundle from database
        if (!$bundle = $bundles->findOneBy(array('id' => $message['bundle_id']))) {
            if ($this->logger) {
                $this->logger->warn(sprintf('Unable to retrieve bundle #%d', $message['bundle_id']));
            }

            return;
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Retrieved bundle %s', $bundle->getName()));
        }

        try {
            if (!$this->githubRepoApi->update($bundle)) {
                if ($this->logger) {
                    $this->logger->warn(sprintf('Update failed, bundle "%s" will be removed', $bundle->getName()));
                }
                $this->removeBundle($bundle);

                return;
            }

            $this->updateContributors($bundle);
            $score = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Score')->setScore(new \DateTime(), $bundle, $bundle->getScore());
            $this->em->persist($score);
            $this->em->flush();

            if ($bundle->getUsesTravisCi()) {
                $this->travis->update($bundle);
            }

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->err('['.get_class($e).'] '.$e->getMessage());
            }
        }
    }

    /**
     * Takes a bundle and update its contributors
     *
     * @param Bundle $bundle
     */
    private function updateContributors(Bundle $bundle)
    {
        $contributorNames = $this->githubRepoApi->getContributorNames($bundle);

        $contributors = array();
        foreach ($contributorNames as $contributorName) {
            $contributors[] = $this->users->getOrCreate($contributorName);
        }

        try {
            $bundle->setContributors($contributors);
            $this->em->flush();
        } catch(\PDOException $e) {
            if ($this->logger) {
                $this->logger->err($e->getMessage());
            }
        }

        if ($this->logger) {
            $this->logger->info(sprintf('%d contributor(s) have been retrieved for bundle %s', sizeof($contributors), $bundle->getName()));
        }
    }

    /**
     * Removes a specified bundle
     *
     * @param Knp\Bundle\Entity\Bundle $bundle
     */
    protected function removeBundle(Bundle $bundle)
    {
        $bundle->getUser()->removeBundle($bundle);
        $this->em->remove($bundle);
        $this->em->flush();

        // @todo also delete folder

        if ($this->logger) {
            $this->logger->warn('Bundle "%s" was deleted', $bundle->getName());
        }
    }

}
