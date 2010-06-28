<?php

require_once __DIR__.'/../symfony2bundles/FrontKernel.php';

$kernel = new FrontKernel('prod', false);
$kernel->handle()->send();
