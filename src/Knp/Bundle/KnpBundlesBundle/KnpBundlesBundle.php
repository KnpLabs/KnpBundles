<?php

namespace Knp\Bundle\KnpBundlesBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Knp\Bundle\KnpBundlesBundle\DependencyInjection\KnpBundlesExtension;
use Knp\Bundle\KnpBundlesBundle\DependencyInjection\Compiler\FinderPass;

class KnpBundlesBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new KnpBundlesExtension());
        $container->addCompilerPass(new FinderPass());
    }
}
