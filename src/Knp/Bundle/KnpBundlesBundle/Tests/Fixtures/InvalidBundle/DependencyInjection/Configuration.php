<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Fixtures\InvalidBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('vendor_invalid_bundle');

        $rootNode
            ->children()
                ->scalarNode('app_id')->defaultValue(MissingClass::AndConstCalled)->end()
            ->end();

        return $treeBuilder;
    }
}
