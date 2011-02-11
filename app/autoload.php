<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                       => __DIR__.'/../vendor/symfony/src',
    'Knplabs'                       => __DIR__.'/../src',
    'Application'                   => __DIR__.'/../src',
    'Bundle'                        => __DIR__.'/../src',
    'Doctrine\DBAL\Migrations'      => __DIR__.'/../vendor/doctrine-migrations/lib',
    'Doctrine\Common'               => __DIR__.'/../vendor/doctrine-common/lib',
    'Doctrine\Common\DataFixtures'  => __DIR__.'/../vendor/doctrine-data-fixtures/lib',
    'Doctrine\DBAL'                 => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine'                      => __DIR__.'/../vendor/doctrine/lib',
    'Zend'                          => __DIR__.'/../vendor/zend/library',
    'Goutte'                        => __DIR__.'/../vendor/goutte/src',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/../vendor/twig/lib',
));
$loader->register();

// Require php-github-api
require_once(__DIR__.'/../vendor/php-github-api/lib/phpGitHubApi.php');

// Require php-git-repo
require_once(__DIR__.'/../vendor/php-git-repo/lib/phpGitRepo.php');
