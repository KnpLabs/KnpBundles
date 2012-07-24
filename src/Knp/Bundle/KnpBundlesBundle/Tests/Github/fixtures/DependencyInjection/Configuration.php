<?php

namespace Vendor\FixtureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vendor_bundle_name');

        $rootNode
            ->fixXmlConfig('permission', 'permissions')
            ->children()
                ->scalarNode('app_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('file')->defaultNull()->end()
                ->scalarNode('cookie')->defaultFalse()->end()
                ->scalarNode('domain')->defaultNull()->end()
                ->scalarNode('alias')->defaultNull()->end()
                ->scalarNode('logging')->defaultValue('%kernel.debug%')->end()
                ->scalarNode('culture')->defaultValue('en_US')->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api')->defaultValue('Vendor\FixtureBundle\APIKey')->end()
                        ->scalarNode('type')->defaultValue('Vendor\FixtureBundle\Type')->end()
                    ->end()
                ->end()
                ->arrayNode('permissions')->prototype('scalar')->end()
            ->end();

        return $treeBuilder;
    }
}