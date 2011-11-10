<?php

namespace Knp\Bundle\KnpBundlesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add all services with the "knp_bundles.finder" tag and add them to the
 * aggregate finder
 *
 * @package KnpBundles
 */
class FinderPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('knp_bundles.finder')) {
            return;
        }

        $finderDef = $container->getDefinition('knp_bundles.finder');

        foreach ($container->findTaggedServiceIds('knp_bundles.finder') as $id => $attributes) {
            $finderDef->addMethodCall('addFinder', array(new Reference($id)));
        }
    }
}
