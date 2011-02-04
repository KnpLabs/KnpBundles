<?php

namespace Knplabs\Symfony2BundlesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Symfony2BundlesExtension extends Extension
{

    public function configLoad($config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('menu.xml');
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
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'symfony2bundles';
    }

}
