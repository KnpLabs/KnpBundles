<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use Knp\Bundle\KnpBundlesBundle\Github;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\ORM\EntityManager;

class UpdateBundleConsumer implements ConsumerInterface
{
    private $logger;

    private $container;

    private $manager;

    private $users;

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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
        // Here we should probably call a bundle_updater service with the data
        // from the message
        
        $message = unserialize($msg);

        if (!isset($message['bundle_id'])) {
            
            throw new \InvalidArgumentException('The bundle id is missing!');
        }

        $bundles = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        // Retrieve Bundle from database
        $bundle = $bundles->findOneBy(array('id' => $message['bundle_id']));

        if ($this->logger) {
            $this->logger->info(sprintf('Retrieved bundle %s', $bundle->getName()));
        }

        if (!$this->githubRepoApi->update($bundle)) {
            // Update failed, bundle must be removed
            $bundle->getUser()->removeBundle($bundle);
            $this->em->remove($bundle);
            $this->em->flush();

            return false;
        } else {
            $score = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Score')->setScore(new \DateTime(), $bundle, $bundle->getScore());
            $this->em->persist($score);
        }

        $this->em->flush();

        $contributorNames = $this->githubRepoApi->getContributorNames($bundle);
        $contributors = array();
        foreach ($contributorNames as $contributorName) {
            $contributors[] = $this->getOrCreateUser($contributorName);
        }

        $bundle->setContributors($contributors);
        $this->em->flush();
        
        if ($bundle->getUsesTravisCi()) {
            $this->travis->update($bundle);
        }
    }

    /**
     * Pretty sure this should move into a dedicated service.
     */
    public function getOrCreateUser($username)
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