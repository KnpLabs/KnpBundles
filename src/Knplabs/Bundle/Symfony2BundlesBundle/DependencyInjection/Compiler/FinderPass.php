<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add all services with the "symfony2bundles.finder" tag and add them to the
 * aggregate finder
 *
 * @package Symfony2Bundles
 */
class FinderPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('symfony2bundles.finder')) {
            return;
        }

        $finderDef = $container->getDefinition('symfony2bundles.finder');

        foreach ($container->findTaggedServiceIds('symfony2bundles.finder') as $id => $attributes) {
            $finderDef->addMethodCall('addFinder', array(new Reference($id)));
        }
    }
}
