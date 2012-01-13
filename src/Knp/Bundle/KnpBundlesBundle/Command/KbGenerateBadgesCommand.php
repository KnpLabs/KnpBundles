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
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $bundleRepository = $em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');

        try {
            foreach ($bundleRepository->findAll() as $bundle) {
                $this->getContainer()->get('knp_bundles.badges_generator')->generate(
                    $bundle->getUserName().'/'.$bundle->getName(),
                    $bundle->getScore,
                    $bundle->getNbRecommenders()
                );
            }
        } catch (ImageNotSavedException $e) {
            $output->writeln('<error>Error occured during an image saving</error>');
        }
    }
}
