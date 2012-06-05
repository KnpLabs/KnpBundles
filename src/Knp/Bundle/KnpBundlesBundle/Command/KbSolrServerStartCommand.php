<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class KbSolrServerStartCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('kb:solr:start')
            ->setDescription('Start SOLR for given enviroment')
            ->addOption('solr-path', 'p', InputOption::VALUE_OPTIONAL, 'path to solr (where start.jar is localized)', '/opt/solr/example')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'If set show command but not execute it')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dry-run')) {
            $output->writeln(sprintf('<info>%s</info>', $this->createRunSolrCommand($input)));

            return 0;
        }

        if ($this->solrIsRunning()) {
            $output->writeln(sprintf('<info>%s %d</info>', 'Solr is running. Pid: ', $this->getSolrPid()));

            return 0;
        }

        $output->writeln(sprintf('<info>%s</info>', 'Starting solr in background process'));

        $process = new Process($this->createRunSolrCommand($input));
        $process->run();

        $output->writeln(sprintf('<info>Pid: %d</info>', $this->getSolrPid()));
    }

    /**
     * @return boolean
     */
    private function solrIsRunning()
    {
        return (boolean) $this->getSolrPid();
    }

    /**
     * Get SOLR pid
     */
    private function getSolrPid()
    {
        $properties = array();
        foreach ($this->getPropertiesArray() as $key => $property) {
             $properties[] = $key.'='.$property;
        }

        $process = new Process(sprintf('ps aux | grep \\\\%s | grep -v grep | awk \'{ print $2 }\'', implode('| grep \\\\', $properties)));
        $process->run();
        $pid = $process->getOutput();

        return (integer) $pid;
    }

    /**
     * Create and return SOLR start command
     *
     * @param InputInterface $input
     * @return string
     */
    private function createRunSolrCommand(InputInterface $input)
    {
        $solrPath = $input->getOption('solr-path');

        return sprintf('(cd %s; java -jar %s start.jar ) 1> /dev/null 2> /dev/null &', $solrPath, $this->buildProperties());
    }

    /**
     * Build SOLR start.jar properties
     *
     * @return string
     */
    private function buildProperties()
    {
        $properties = array();
        foreach ($this->getPropertiesArray() as $key => $property) {
             $properties[] = $key.'='.$property;
        }

        return implode(' ', $properties);
    }

    /**
     * @return array
     */
    private function getPropertiesArray()
    {
        return array(
            '-Djetty.port'     => $this->getContainer()->get('solarium.client')->getAdapter()->getPort(),
            '-Dsolr.solr.home' => $this->getContainer()->get('kernel')->getBundle('KnpBundlesBundle')->getPath().'/Resources/solr'
        );
    }
}
