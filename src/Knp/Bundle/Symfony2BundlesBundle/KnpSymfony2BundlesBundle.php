<?php

namespace Knp\Bundle\Symfony2BundlesBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Knp\Bundle\Symfony2BundlesBundle\DependencyInjection\KnpSymfony2BundlesExtension;
use Knp\Bundle\Symfony2BundlesBundle\DependencyInjection\Compiler\FinderPass;

class KnpSymfony2BundlesBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new KnpSymfony2BundlesExtension());
        $container->addCompilerPass(new FinderPass());
    }
}
