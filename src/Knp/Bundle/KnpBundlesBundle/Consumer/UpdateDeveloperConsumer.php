<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

use Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdater;

class UpdateDeveloperConsumer implements ConsumerInterface
{
    protected $developerUpdater;

    public function __construct(DeveloperUpdater $developerUpdater)
    {
        $this->developerUpdater = $developerUpdater;
    }

    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->body, true);
        $name = $message['name'];
        $this->developerUpdater->performDeveloperUpdate($name);
    }
}
