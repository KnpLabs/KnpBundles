<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Producer;


use Knp\Bundle\KnpBundlesBundle\Producer\ExecutorProducer;

class ExecutorProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testExecution()
    {
        $consumer = $this->getMock('OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface');
        $consumer->expects($this->once())
            ->method('execute');

        $producer = new ExecutorProducer($consumer);

        $producer->publish('a message');
    }
}
