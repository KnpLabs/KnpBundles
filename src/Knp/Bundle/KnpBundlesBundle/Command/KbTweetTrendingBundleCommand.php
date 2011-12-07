<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Twitterer\Exception\TrendingBundleNotFoundException;

class KbTweetTrendingBundleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:tweet:trending-bundle')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $twitterer = $this->getContainer()->get('knp_bundles.trending_bundle_twitterer');
            $twitterer->tweet();
        } catch (TrendingBundleNotFoundException $e) {
            $output->writeln('<error>Trending bundle not found</error>');
        }
    }
}
