<?php

namespace Knp\Bundle\KnpBundlesBundle\Producer;


interface ProducerInterface
{
    public function publish($msgBody, $routingKey = '');
}
