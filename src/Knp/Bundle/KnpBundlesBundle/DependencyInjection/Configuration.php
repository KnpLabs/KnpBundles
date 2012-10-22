<?php

namespace Knp\Bundle\KnpBundlesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the extension
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $rootNode = $builder->root('knp_bundles');
        $rootNode
            ->children()
                ->arrayNode('github_client')
                    ->children()
                        ->scalarNode('client_id')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('client_secret')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('limit')
                            ->defaultValue(5000)
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('twitter_client')
                    ->children()
                        ->scalarNode('consumer_key')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('consumer_secret')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('oauth_token')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('oauth_token_secret')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sensiolabs_client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('client_id')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('client_secret')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('git_bin')
                    ->defaultValue('/usr/bin/git')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('generate_badges')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('driver')
                            ->defaultValue('gd')
                            ->validate()
                                ->ifNotInArray(array('gd', 'imagick', 'gmagick'))
                                ->thenInvalid('Invalid imagine driver specified: %s')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('misc')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('finder_limit')
                            ->defaultValue(2000)
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('evolution_period')
                            ->defaultValue(14)
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('trending_bundle')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('idle_period')
                            ->defaultValue(30) #days
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('template')
                            ->defaultValue("Discover {name}, today's trending #Symfony2 bundle {url} on #KnpBundles")
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('min_score_diff')
                            ->defaultValue(3)
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('min_score_threshold')
                            ->defaultValue(25)
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
