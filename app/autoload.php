<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                       => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    'Knp\Bundle\KnpBundlesBundle'   => __DIR__.'/../src',
    'Knp\Bundle'                    => __DIR__.'/../vendor/bundles',
    'Knp\Menu'                      => __DIR__.'/../vendor/knp-menu/src',
    'Knp\Component'                 => __DIR__.'/../vendor/knp-components/src',
    'Ornicar\GravatarBundle'        => __DIR__.'/../vendor/bundles',
    'Doctrine\DBAL\Migrations'      => __DIR__.'/../vendor/doctrine-migrations/lib',
    'Doctrine\Common'               => __DIR__.'/../vendor/doctrine-common/lib',
    'Doctrine\Common\DataFixtures'  => __DIR__.'/../vendor/doctrine-data-fixtures/lib',
    'Doctrine\DBAL'                 => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine'                      => __DIR__.'/../vendor/doctrine/lib',
    'JMS'                           => __DIR__.'/../vendor/bundles',
    'Monolog'                       => __DIR__.'/../vendor/monolog/src',
    'Goutte'                        => __DIR__.'/../vendor/goutte/src',
    'Zend'                          => __DIR__.'/../vendor/zf/library',
    'Etcpasswd'                     => __DIR__.'/../vendor/bundles',
    'Buzz'                          => __DIR__.'/../vendor/buzz/lib',
    'Inori'                         => __DIR__.'/../vendor/bundles',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_'              => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'                         => __DIR__.'/../vendor/twig/lib',
    'Github_'                       => __DIR__.'/../vendor/php-github-api/lib',
    'PHPGit_'                       => __DIR__.'/../vendor/php-git-repo/lib'
));
$loader->register();

// Registering the annotations
AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
AnnotationRegistry::registerFile(
    __DIR__.'/../vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);
