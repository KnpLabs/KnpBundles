<?php

namespace Knp\Bundle\KnpBundlesBundle\Updater;

use Knp\Bundle\KnpBundlesBundle\Producer\RabbitProducer;
use Knp\Bundle\KnpBundlesBundle\Updater\DeveloperUpdaterStrategyInterface;

class DeveloperUpdaterRabbitMqStrategy implements DeveloperUpdaterStrategyInterface
{
    protected $updateDeveloperProducer;

    public function __construct(RabbitProducer $updateDeveloperProducer)
    {
        $this->updateDeveloperProducer = $updateDeveloperProducer;
    }

    public function updateDeveloperByName($name)
    {
        $this->updateDeveloperProducer->publish(json_encode(array('name' => $name)));
    }
}

