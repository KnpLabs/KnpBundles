<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

$loader = new Symfony\Component\HttpFoundation\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                       => __DIR__.'/vendor/Symfony/src',
    'Application'                   => __DIR__,
    'Bundle'                        => __DIR__,
    'Doctrine\DBAL\Migrations'      => __DIR__.'/vendor/DoctrineMigrations/lib',
    'Doctrine\Common'               => __DIR__.'/vendor/Doctrine/lib/vendor/doctrine-common/lib',
    'Doctrine\Common\DataFixtures'  => __DIR__.'/vendor/DoctrineDataFixtures/lib',
    'Doctrine\DBAL'                 => __DIR__.'/vendor/doctrine-dbal/lib',
    'Doctrine'                      => __DIR__.'/vendor/Doctrine/lib',
    'Zend'                          => __DIR__.'/vendor/Zend/library',
));

$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/vendor//twig/lib',
));
$loader->register();

// Require php-github-api
require_once(__DIR__.'/vendor/php-github-api/lib/phpGitHubApi.php');

// Require php-git-repo
require_once(__DIR__.'/vendor/php-git-repo/lib/phpGitRepo.php');
