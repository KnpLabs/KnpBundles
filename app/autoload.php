<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                       => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    'Knp'                           => array(__DIR__.'/../src', __DIR__.'/../vendor/bundles'),
    'Bundle'                        => __DIR__.'/../vendor/bundles',
    'Doctrine\DBAL\Migrations'      => __DIR__.'/../vendor/doctrine-migrations/lib',
    'Doctrine\Common'               => __DIR__.'/../vendor/doctrine-common/lib',
    'Doctrine\Common\DataFixtures'  => __DIR__.'/../vendor/doctrine-data-fixtures/lib',
    'Doctrine\DBAL'                 => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine'                      => __DIR__.'/../vendor/doctrine/lib',
    'Zend'                          => array(__DIR__.'/../vendor', __DIR__.'/../vendor/zend-registry'),
    'Monolog'                       => __DIR__.'/../vendor/monolog/src',
    'Goutte'                        => __DIR__.'/../vendor/goutte/src',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/../vendor/twig/lib',
    'Github_'          => __DIR__.'/../vendor/php-github-api/lib'
));
$loader->register();

// Require php-git-repo
require_once(__DIR__.'/../vendor/php-git-repo/lib/phpGitRepo.php');
