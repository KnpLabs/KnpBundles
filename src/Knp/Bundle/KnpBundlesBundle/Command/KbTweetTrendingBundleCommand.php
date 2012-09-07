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
            /* @var $twitterer \Knp\Bundle\KnpBundlesBundle\Twitterer\TrendingBundleTwitterer */
            $twitterer = $this->getContainer()->get('knp_bundles.trending_bundle_twitterer');
            $trendingBundle = $twitterer->tweet();

            $output->writeln(sprintf('Today trending bundle - %s has been tweeted', $trendingBundle->getName()));
        } catch (TrendingBundleNotFoundException $e) {
            $output->writeln('<error>Trending bundle not found</error>');
        }
    }
}
