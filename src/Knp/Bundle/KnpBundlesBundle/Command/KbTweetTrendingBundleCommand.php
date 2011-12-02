<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

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
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        if ($trendingBundle = $em->getRepository('KnpBundlesBundle:Bundle')->getMostTrendingBundle()) {
            $bundleName = $trendingBundle->getName();
            $bundleUrl = 'bundles.knplabs.org/' . $trendingBundle->getUsername() . '/' . $bundleName;
            $twitter = $this->getContainer()->get('twitter_app');
            $twitter->tweet('Discover ' . $bundleName . ', ' .
                            'today\'s trending #Symfony2 bundle ' . $bundleUrl . ' #KnpBundles');
        }
    }
}
