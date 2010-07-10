<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Foundation/UniversalClassLoader.php';

use Symfony\Foundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                   => __DIR__.'/vendor/Symfony/src',
    'Application'               => __DIR__,
    'Bundle'                    => __DIR__,
    'Doctrine\DBAL\Migrations'  => __DIR__.'/vendor/migrations/lib',
    'Doctrine\DBAL'             => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-dbal/lib',
    'Doctrine\Common'           => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-common/lib',
    'Doctrine'                  => __DIR__.'/vendor/doctrine/lib',
    'Zend'                      => __DIR__.'/vendor/Zend/library',
));
$loader->register();

// Require php-github-api
require_once(__DIR__.'/vendor/php-github-api/lib/phpGitHubApi.php');
