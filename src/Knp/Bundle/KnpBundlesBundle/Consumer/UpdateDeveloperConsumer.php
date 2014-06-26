<?php

namespace Knp\Bundle\KnpBundlesBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

use Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdaterManager;

class UpdateDeveloperConsumer implements ConsumerInterface
{
    protected $developerUpdaterManager;

    public function __construct(DeveloperUpdaterManager $developerUpdaterManager)
    {
        $this->developerUpdaterManager = $developerUpdaterManager;
    }

    public function execute(AMQPMessage $msg)
    {
        $message = json_decode($msg->body, true);
        $name = $message['name'];
        $this->developerUpdaterManager->performDeveloperUpdate($name);
    }
}
