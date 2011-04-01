<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),

            // enable third-party bundles
            new Symfony\Bundle\ZendBundle\ZendBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle(),

            // register your bundles here
            new Knplabs\Bundle\MarkdownBundle\KnplabsMarkdownBundle(),
            new Knplabs\Bundle\TimeBundle\KnplabsTimeBundle(),
            new Knplabs\Bundle\MenuBundle\KnplabsMenuBundle(),
            new Bundle\GravatarBundle\GravatarBundle(),

            // register your applications here
            new Knplabs\Bundle\Symfony2BundlesBundle\KnplabsSymfony2BundlesBundle()
        );

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerRootDir()
    {
        return __DIR__;
    }

    /**
     * Returns the config_{environment}_local.yml file or
     * the default config_{environment}.yml if it does not exist.
     * Useful to override development password.
     *
     * @param string Environment
     * @return The configuration file path
     */
    protected function getLocalConfigurationFile($environment)
    {
        $basePath = __DIR__.'/config/config_';
        $file = $basePath.$environment.'_local.yml';

        if(\file_exists($file)) {
            return $file;
        }

        return $basePath.$environment.'.yml';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getLocalConfigurationFile($this->getEnvironment()));
    }
}
