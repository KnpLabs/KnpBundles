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

use Github\Client;

class KnpBundlesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('finder.xml');
        $loader->load('model.xml');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('buzz.yml');
        $loader->load('twig.yml');
        $loader->load('menus.yml');
        $loader->load('services.yml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setAlias('knp_bundles.imagine', new Alias('knp_bundles.imagine.'.strtolower($config['generate_badges']['driver']), false));

        $container->setParameter('knp_bundles.git_bin', $config['git_bin']);

        $bundleManager = $container->getDefinition('knp_bundles.bundle.manager');
        $bundleManager->addMethodCall('setOption', array('min_score_diff', $config['trending_bundle']['min_score_diff']));
        $bundleManager->addMethodCall('setOption', array('min_score_threshold', $config['trending_bundle']['min_score_threshold']));

        $twitterClient = $container->getDefinition('knp_bundles.trending_bundle_twitterer');
        $twitterClient->addMethodCall('setTwitterParams', array(
            $config['trending_bundle']['template'],
            $config['trending_bundle']['idle_period'],
            $config['twitter_client']
        ));

        // setup GitHub API client settings
        $githubClient = $container->getDefinition('knp_bundles.github_client');
        $githubClient->addMethodCall('setOption', array('api_limit', $config['github_client']['limit']));
        $githubClient->addMethodCall('authenticate', array(
            $config['github_client']['client_id'],
            $config['github_client']['client_secret'],
            Client::AUTH_URL_CLIENT_ID
        ));
    }
}
