<?php

namespace Knp\Bundle\KnpBundlesBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class KnpBundlesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('finder.xml');
        $loader->load('paginator.xml');
        $loader->load('model.xml');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('twig.yml');
        $loader->load('menus.yml');
        $loader->load('services.yml');

        $processor = new Processor();
        $config = $processor->process($this->getConfigTree(), $configs);
        $container->setParameter('knp_bundles.git_bin', $config['git_bin']);

        // Set default image process lib
        $driver = 'gd';

        if (isset($config['generate_badges']['driver'])) {
            $driver = strtolower($config['generate_badges']['driver']);
        }

        if (!in_array($driver, array('gd', 'imagick'))) {
            throw new \InvalidArgumentException('Invalid imagine driver specified');
        }

        $container->setAlias('knp_bundles.imagine', new Alias('knp_bundles.imagine.'.$driver));
    }

    private function getConfigTree()
    {
        $tb = new TreeBuilder();

        $tb
            ->root('knp_bundles')
                ->children()
                    ->scalarNode('git_bin')->defaultValue('/usr/bin/git')->cannotBeEmpty()->end()
                ->end()
                ->children()
                    ->arrayNode('generate_badges')
                        ->children()
                            ->scalarNode('driver')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb->buildTree();
    }
}