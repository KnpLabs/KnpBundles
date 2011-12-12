<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;

class UpdateBundleConsumer implements ConsumerInterface
{

    private $container;

    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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

        $bundles = $this->manager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        // Retrieve Bundle from database
        $bundle = $bundles->findOneBy(array('id' => $message['bundle_id']));


    }

}