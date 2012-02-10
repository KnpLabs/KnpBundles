<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\Common\Persistence\ObjectManager;

/**
* 
*/
class GithubHookConsumer implements ConsumerInterface
{
    /**
     * @var OldSound\RabbitMqBundle\RabbitMq\Producer
     */
    private $producer;

    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;

    public function __construct(ObjectManager $manager, Producer $producer)
    {
        $this->producer = $producer;
        $this->manager = $manager;
    } 
    
    /**
     * {@inheritDoc}
     */
    public function execute($msg)
    {
        if ($this->logger) {
            $this->logger->info('[GithubHookConsumer] Received a github post push hook');
        }

        if (is_null($message = json_decode($msg->body))) {
            if ($this->logger) {
                $this->logger->err('[GithubHookConsumer] Unable to decode payload');
            }
            
            return;
        }
        $payload = $message->payload;

        $bundles = $this->manager->getRepository('KnpBundlesBundle:Bundle');

        $bundle = $bundles->findOneBy(array(
            'name' => $payload->repository->name,
            'username' => $payload->repository->owner->name
        ));

        if (!$bundle) {
            if ($this->logger) {
                $this->logger->warn(sprintf('[GithubHookConsumer] unknown bundle %s/%s', 
                  $payload->repository->name, 
                  $payload->repository->owner->name));
            }

            return;
        }

        $this->producer->publish(serialize(array('bundle_id' => $bundle->getId())));
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
