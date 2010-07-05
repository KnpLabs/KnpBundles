<?php

require_once __DIR__.'/vendor/Symfony/src/Symfony/Foundation/UniversalClassLoader.php';

use Symfony\Foundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                    => __DIR__.'/vendor/Symfony/src',
    'Application'                => __DIR__,
    'Bundle'                     => __DIR__,
    'Doctrine\\Common'           => __DIR__.'/vendor/MongoDbOdm/lib/vendor/doctrine-common/lib',
    'Doctrine\\ODM\\MongoDB'     => __DIR__.'/vendor/MongoDbOdm/lib',
    'Zend'                       => __DIR__.'/vendor/zend/library',
));
$loader->register();

