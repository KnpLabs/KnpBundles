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

        $driver = strtolower($config['generate_badges']['driver']);

        $container->setAlias('knp_bundles.imagine', new Alias('knp_bundles.imagine.'.$driver));
    }

    private function getConfigTree()
    {
        $tb = new TreeBuilder();

        $tb
            ->root('knp_bundles')
                ->children()
                    ->scalarNode('git_bin')->defaultValue('/usr/bin/git')->cannotBeEmpty()->end()
                    ->arrayNode('generate_badges')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('driver')->defaultValue('gd')
                            ->validate()
                                ->ifTrue(function($v) { return !in_array($v, array('gd', 'imagick', 'gmagick')); })
                                ->thenInvalid('Invalid imagine driver specified: %s')
                            ->end()
                        ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb->buildTree();
    }
}