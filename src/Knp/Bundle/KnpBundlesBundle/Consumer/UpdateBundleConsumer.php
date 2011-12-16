<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use Knp\Bundle\KnpBundlesBundle\Github;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\ORM\EntityManager;

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
     * @var array
     */
    private $users;

    /**
     * @param Doctrine\ORM\EntityManager $em
     * @param string $gitRepoDir
     * @param string $gitBin
     */
    public function __construct(EntityManager $em, $gitRepoDir, $gitBin)
    {
        $output = new NullOutput();

        $this->em = $em;

        $githubClient = new \Github_Client();
        $githubSearch = new Github\Search($githubClient, new \Goutte\Client(), $output);
        $githubUserApi = new Github\User($githubClient, $output);

        $gitRepoManager = new Git\RepoManager($gitRepoDir, $gitBin);
        $this->githubRepoApi = new Github\Repo($githubClient, $output, $gitRepoManager);
        $this->travis = new Travis($output);

        $this->users = array();
        foreach ($this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\User')->findAll() as $user) {
            $this->users[strtolower($user->getName())] = $user;
        }
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
     * @param string serialized Message
     */
    public function execute($msg)
    {
        // Retrieve informations from the message
        $message = unserialize($msg);

        if (!isset($message['bundle_id'])) {
            
            throw new \InvalidArgumentException('The bundle id is missing!');
        }

        $bundles = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        // Retrieve Bundle from database
        if (!$bundle = $bundles->findOneBy(array('id' => $message['bundle_id'])))
        {

            throw new \InvalidArgumentException(sprintf('Unable to retrieve bundle with id %d', $message['bundle_id']));
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Retrieved bundle %s', $bundle->getName()));
            $this->logger->info('Updating bundle');
        }

        if (!$this->githubRepoApi->update($bundle)) {
            // Update failed, bundle must be removed
            $bundle->getUser()->removeBundle($bundle);
            $this->em->remove($bundle);
            $this->em->flush();

            if ($this->logger) {
                $this->logger->warn('Update failed, bundle has been removed');
            }

            return false;
        } 

        $this->updateContributors($bundle);

        $score = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Score')->setScore(new \DateTime(), $bundle, $bundle->getScore());
        $this->em->persist($score);
        $this->em->flush();

        if ($bundle->getUsesTravisCi()) {
            $this->travis->update($bundle);
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
            $contributors[] = $this->getOrCreateUser($contributorName);
        }

        $bundle->setContributors($contributors);
        $this->em->flush();

        if ($this->logger) {
            $this->logger->info(sprintf('%d contributor(s) have been retrieved for bundle %s', sizeof($contributors), $bundle->getName()));
        }
    }

    /**
     * Retrieve or create a user
     * @todo move into a dedicated service.
     * 
     * @param string $username
     * @return Knp\Bundle\KnpBundlesBundle\Entity\User
     */
    private function getOrCreateUser($username)
    {
        if (isset($this->users[strtolower($username)])) {
            $user = $this->users[strtolower($username)];
        } else {

            if (!$user = $this->githubUserApi->import($username)) {
                throw new UserNotFoundException();
            }

            $this->users[strtolower($user->getName())] = $user;
            $this->em->persist($user);
        }

        return $user;
    }

}