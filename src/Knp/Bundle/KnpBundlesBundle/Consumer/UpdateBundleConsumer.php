<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateBundleConsumer implements ConsumerInterface
{
    private $container;

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
        file_put_contents(__DIR__.'/'.\uniqid(), 'yop');
    }

}