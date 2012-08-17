<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Knp\Bundle\KnpBundlesBundle\Utils\SolrUtils;

/**
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class KbSolrServerStartCommand extends ContainerAwareCommand
{
    /**
     * @var SolrUtils
     */
    protected $utils;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('kb:solr:start')
            ->setDescription('Start SOLR for given enviroment')
            ->addOption('solr-path', 'p', InputOption::VALUE_OPTIONAL, 'path to solr (where start.jar is localized)', '/opt/solr/example')
            ->addOption('show-commands-only', null, InputOption::VALUE_NONE, 'If set show command but not execute it')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->utils = $this->getContainer()->get('knp_bundles.utils.solr');

        if ($input->getOption('show-commands-only')) {
            $output->writeln(sprintf('<info>%s</info>', $this->createRunSolrCommand($input)));

            return 0;
        }

        if ($this->utils->isSolrRunning()) {
            $output->writeln(sprintf('<info>%s %d</info>', 'Solr is running. Pid: ', $this->utils->getSolrPid()));

            return 0;
        }

        $output->writeln(sprintf('<info>%s</info>', 'Starting solr in background process'));

        $process = new Process($this->createRunSolrCommand($input));
        $process->run();

        $output->writeln(sprintf('<info>Pid: %d</info>', $this->utils->getSolrPid()));

        return 0;
    }

    /**
     * Create and return SOLR start command
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function createRunSolrCommand(InputInterface $input)
    {
        $solrPath = $input->getOption('solr-path');

        return sprintf('(cd %s; java -jar %s start.jar ) 1> /dev/null 2> /dev/null &', $solrPath, $this->utils->buildProperties());
    }
}
