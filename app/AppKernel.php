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
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),

            // enable third-party bundles
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Etcpasswd\OAuthBundle\EtcpasswdOAuthBundle(),
            new Inori\TwitterAppBundle\InoriTwitterAppBundle(),
            new Igorw\FileServeBundle\IgorwFileServeBundle(),

            // register your bundles here
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
            new Knp\Bundle\TimeBundle\KnpTimeBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\ZendCacheBundle\KnpZendCacheBundle(),
            new Knp\Bundle\DisqusBundle\KnpDisqusBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Ornicar\GravatarBundle\OrnicarGravatarBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),

            // register your applications here
            new Knp\Bundle\KnpBundlesBundle\KnpBundlesBundle(),
        );

        if ('test' === $this->getEnvironment()) {
            $bundles[] = new Behat\BehatBundle\BehatBundle();
            $bundles[] = new Behat\MinkBundle\MinkBundle();
        }

        if ($this->isDebug()) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
