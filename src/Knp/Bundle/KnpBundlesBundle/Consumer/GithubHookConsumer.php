<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\Common\Persistence\ObjectManager;

class GithubHookConsumer implements ConsumerInterface
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ObjectManager $manager
     * @param Producer      $producer
     */
    public function __construct(ObjectManager $manager, Producer $producer)
    {
        $this->producer = $producer;
        $this->manager  = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($msg)
    {
        if ($this->logger) {
            $this->logger->info('[GithubHookConsumer] Received a github post push hook');
        }

        if (null === $message = json_decode($msg->body)) {
            if ($this->logger) {
                $this->logger->err('[GithubHookConsumer] Unable to decode payload');
            }

            return;
        }

        $payload = $message->payload;
        $bundle  = $this->manager->getRepository('KnpBundlesBundle:Bundle')->findOneBy(array(
            'name'     => $payload->repository->name,
            'username' => $payload->repository->owner->name
        ));

        if (!$bundle) {
            if ($this->logger) {
                $this->logger->warn(
                    sprintf('[GithubHookConsumer] unknown bundle %s/%s', $payload->repository->name, $payload->repository->owner->name)
                );
            }

            return;
        }

        $this->producer->publish(serialize(array('bundle_id' => $bundle->getId())));
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
