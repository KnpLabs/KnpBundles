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
 * Create missing proxies
 */
class ProxyCreateCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        ->setDefinition(array())
        ->setName('proxy:create');
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        mkdir($this->container->getParameter('kernel.cache_dir').'/Proxies');
        $output->writeLn('Done');
    }
}
