<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class KnplabsSymfony2BundlesExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('finder.xml');
        $loader->load('paginator.xml');
        $loader->load('model.xml');
        $loader->load('controller.xml');
        $loader->load('menu.xml');

        $processor = new Processor();
        $config = $processor->process($this->getConfigTree(), $configs);
        $container->setParameter('symfony2bundles.git_bin', $config['git_bin']);
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://www.symfony.com/schema/dic/symfony';
    }

    private function getConfigTree()
    {
        $tb = new TreeBuilder();

        $tb
            ->root('knplabs_symfony2_bundles')
                ->children()
                    ->scalarNode('git_bin')->defaultValue('/usr/bin/git')->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;

        return $tb->buildTree();
    }
}
