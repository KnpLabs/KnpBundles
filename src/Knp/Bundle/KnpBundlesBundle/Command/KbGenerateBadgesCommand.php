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
        /* @var $badgeGenerator \Knp\Bundle\KnpBundlesBundle\Badge\BadgeGenerator */
        $badgeGenerator = $this->getContainer()->get('knp_bundles.badge_generator');

        $badgesCount = 0;
        /* @var $bundle \Knp\Bundle\KnpBundlesBundle\Entity\Bundle */
        foreach ($bundleRepository->findAll() as $bundle) {
            try {
                $badgeGenerator->generate($bundle);
                ++$badgesCount;
            } catch (ImageNotSavedException $e) {
                $output->writeln('<error>Error occured during an image saving for '.$bundle->getOwnerName().'-'.$bundle->getName().' </error>');
            }
        }

        $output->writeln($badgesCount.' badge(s) '.($badgesCount == 1 ? 'was' : 'were').' generated');
    }
}
