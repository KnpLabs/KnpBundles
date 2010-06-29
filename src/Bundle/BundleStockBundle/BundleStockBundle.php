<?php

namespace Bundle\BundleStockBundle;

use Symfony\Foundation\Bundle\Bundle as BaseBundle;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Components\DependencyInjection\BuilderConfiguration;

class BundleStockBundle extends BaseBundle
{
    public function buildContainer(ContainerInterface $container)
    {
        $configuration = new BuilderConfiguration();

        return $configuration;
    }
}
