<?php

namespace Application\S2bBundle\Command;

use Application\S2bBundle\Document\Bundle;
use Application\S2bBundle\Document\User;
use Symfony\Framework\FoundationBundle\Command\Command as BaseCommand;
use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;

/**
 * Destroy all Mongo collections
 */
class MongoDropCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        ->setDefinition(array())
        ->setName('mongo:drop');
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->container->getDoctrine_odm_mongodb_documentManagerService();
        $dm->getMongo()
        ->selectDB('symfony2bundles')
        ->selectCollection('bundle')
        ->drop();
        $dm->getMongo()
        ->selectDB('symfony2bundles')
        ->selectCollection('user')
        ->drop();
        $output->writeLn('Done');
    }
}
