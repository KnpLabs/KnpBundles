<?php

namespace Knp\Bundle\SitemapBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class GenerateCommand extends DoctrineCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('kb:sitemap:generate')
            ->setDescription('Generate knp-bundles sitemap.')
            ->setDefinition(array(
                new InputOption(
                    'em', null, InputOption::VALUE_OPTIONAL,
                    'Used entity manager.',
                    'default'
                )
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getEntityManager($input->getOption('em'));
        $c = $this->getContainer();

        if (!$c->hasParameter('kb_sitemap.base_url')) {
            throw new \RuntimeException("Sitemap requires base_url parameter [kb_sitemap.base_url] to be available, through config or parameters");
        }

        $dql = <<<___SQL
        SELECT b FROM KnpBundlesBundle:Bundle b
___SQL;
        $q = $em->createQuery($dql);
        $bundles = $q->getArrayResult();

        $sitemapFile = $c->getParameter('kernel.root_dir').'/../web/sitemap.xml';
        file_put_contents($sitemapFile, $c->get('templating')->render(
            'KnpSitemapBundle::sitemap.xml.twig',
            compact('bundles')
        ));

        $output->writeLn('Done');
    }
}