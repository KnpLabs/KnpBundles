<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use Knp\Bundle\KnpBundlesBundle\Github\Repo;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Keyword;
use Knp\Bundle\KnpBundlesBundle\Entity\Owner;
use Knp\Bundle\KnpBundlesBundle\Entity\Score;
use Knp\Bundle\KnpBundlesBundle\Indexer\SolrIndexer;
use Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager;
use Knp\Bundle\KnpBundlesBundle\Travis\Travis;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

use Github\Exception\ApiLimitExceedException;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
    const MAX_GITHUB_TRIALS = 20;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var OwnerManager
     */
    private $ownerManager;

    /**
     * @var SolrIndexer
     */
    private $indexer;

    /**
     * @var Repo
     */
    private $githubRepoApi;

    /**
     * @var Travis
     */
    private $travis;

    /**
     * @param ObjectManager  $em
     * @param OwnerManager   $ownerManager
     * @param Repo           $githubRepoApi
     * @param Travis         $travis
     * @param SolrIndexer    $indexer
     */
    public function __construct(ObjectManager $em, OwnerManager $ownerManager, Repo $githubRepoApi, Travis $travis, SolrIndexer $indexer)
    {
        $this->em = $em;
        $this->ownerManager = $ownerManager;
        $this->githubRepoApi = $githubRepoApi;
        $this->travis = $travis;
        $this->ownerManager = $ownerManager;
        $this->indexer = $indexer;
    }

    /**
     * Set a logger instance
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Callback called from RabbitMQ to update a bundle
     *
     * @param AMQPMessage $msg serialized Message
     */
    public function execute(AMQPMessage $msg)
    {
        // Retrieve informations from the message
        $message = json_decode($msg->body, true);

        if (!isset($message['bundle_id'])) {
            if ($this->logger) {
                $this->logger->err('Bundle id is missing : skip message');
            }

            return;
        }

        $bundles = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        // Retrieve Bundle from database
        /* @var $bundle Bundle */
        if (!$bundle = $bundles->find($message['bundle_id'])) {
            if ($this->logger) {
                $this->logger->warn(sprintf('Unable to retrieve bundle #%d', $message['bundle_id']));
            }

            return;
        }

        if (isset($message['action']) && 'remove' == $message['action']) {
            if ($this->logger) {
                $this->logger->warn(sprintf('Bundle "%s" will be removed', $bundle->getName()));
            }
            $this->removeBundle($bundle);

            return;
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Retrieved bundle %s', $bundle->getName()));
        }

        $keywordRepo = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Keyword');
        $scoreRepo   = $this->em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Score');
        for ($i = 0; $i < self::MAX_GITHUB_TRIALS; $i++) {
            try {
                if (!$this->githubRepoApi->update($bundle)) {
                    if ($this->logger) {
                        $this->logger->warn(sprintf('Update of "%s" failed', $bundle->getName()));
                    }
                }

                $this->indexer->indexBundle($bundle);

                $this->updateContributors($bundle);
                $this->updateKeywords($bundle, $keywordRepo);
                $this->updateScore($bundle, $scoreRepo);

                if ($bundle->getUsesTravisCi()) {
                    $this->travis->update($bundle);
                }
            } catch (ApiLimitExceedException $e) {
                if ($this->logger) {
                    $this->logger->err(sprintf('Bundle %s got a %s for trial %s', $bundle->getName(), $e->getMessage(), $i+1));
                }
                sleep(60 * ($i + 1));
                continue;
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->err('['.get_class($e).' / '.$e->getFile().':'.$e->getLine().'] '.$e->getMessage());
                }
            }
            break;
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
            $contributors[] = $this->ownerManager->createOwner($contributorName, 'unknown');
        }
        $bundle->setContributors($contributors);

        if ($this->logger) {
            $this->logger->info(sprintf('%d contributor(s) have been retrieved for bundle %s', count($contributors), $bundle->getName()));
        }
    }

    /**
     * Updates bundle keywords fetched from composer.json
     *
     * @param Bundle $bundle
     * @param object $repository
     */
    private function updateKeywords(Bundle $bundle, $repository)
    {
        $keywords = $this->githubRepoApi->fetchComposerKeywords($bundle);
        foreach ($keywords as $value) {
            $keyword = $repository->findOneBy(array('value' => $value));
            if (!$keyword) {
                $keyword = new Keyword();
                $keyword->setValue($value);
            }

            $bundle->addKeyword($keyword);
        }
    }

    /**
     * Updates bundle score
     *
     * @param Bundle $bundle
     * @param object $repository
     */
    private function updateScore(Bundle $bundle, $repository)
    {
        if (!$bundle->hasChanges()) {
            return;
        }

        $score = $repository->findOneBy(array(
            'date'   => new \DateTime(),
            'bundle' => $bundle->getId()
        ));

        if (!$score) {
            $score = new Score();
            $score->setBundle($bundle);
        }

        $score->setValue($bundle->getScore());

        $this->em->persist($score);
        $this->em->flush();
    }

    /**
     * Removes a specified bundle
     *
     * @param Bundle $bundle
     */
    protected function removeBundle(Bundle $bundle)
    {
        $owner = $bundle->getOwner();
        if ($owner instanceof Owner) {
            $owner->removeBundle($bundle);
        }

        // remove bundle from search index
        $this->indexer->deleteBundlesIndexes($bundle);

        $this->em->remove($bundle);
        $this->em->flush();

        // @todo also delete folder

        if ($this->logger) {
            $this->logger->warn(sprintf('Bundle "%s" was deleted', $bundle->getName()));
        }
    }
}
