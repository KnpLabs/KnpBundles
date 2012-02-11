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
                ),
                new InputOption(
                    'spaceless', 'spls', InputOption::VALUE_OPTIONAL,
                    'Output spaceless sitemap.',
                    0
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

        $output->write('<info>Fetching resources..</info>' . PHP_EOL);
        $dql = <<<___SQL
        SELECT b.name, b.username, b.updatedAt FROM KnpBundlesBundle:Bundle b
___SQL;
        $q = $em->createQuery($dql);
        $bundles = $q->getArrayResult();

        $dql = <<<___SQL
        SELECT u.name, u.createdAt FROM KnpBundlesBundle:User u
___SQL;
        $q = $em->createQuery($dql);
        $users = $q->getArrayResult();

        $sitemapFile = $c->getParameter('kernel.root_dir').'/../web/sitemap.xml';
        $output->write('<info>Building sitemap...</info>' . PHP_EOL);
        $spaceless = (bool)$input->getOption('spaceless');
        $tpl = $spaceless ? 'KnpSitemapBundle::sitemap.spaceless.xml.twig' : 'KnpSitemapBundle::sitemap.xml.twig';
        $sitemap = $c->get('templating')->render($tpl, compact('bundles', 'users'));
        $output->write("<info>Saving sitemap in [{$sitemapFile}]..</info>" . PHP_EOL);
        file_put_contents($sitemapFile, $sitemap);
        // gzip the sitemap
        if (function_exists('gzopen')) {
            $output->write("<info>Gzipping the generated sitemap [{$sitemapFile}.gz]..</info>" . PHP_EOL);
            $gz = gzopen($sitemapFile.'.gz', 'w9');
            gzwrite($gz, $sitemap);
            gzclose($gz);
        }

        $output->write('<info>Done</info>' . PHP_EOL);
    }
}