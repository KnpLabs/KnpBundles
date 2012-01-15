<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Badge\Exception\ImageNotSavedException;

/**
 * Generates png-badges with bundle name, score and number of recommendations 
 */
class KbGenerateBadgesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->setName('kb:generate:badges')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundleRepository = $this->getContainer()->get('doctrine')->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');
        $badgeGenerator = $this->getContainer()->get('knp_bundles.badge_generator');
        
        foreach ($bundleRepository->findAll() as $bundle) {
            try {
                $badgeGenerator->generate($bundle);
            } catch (ImageNotSavedException $e) {
                $output->writeln('<error>Error occured during an image saving</error>');
            }
        }
    }
}
