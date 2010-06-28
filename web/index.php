<?php

require_once __DIR__.'/../front/FrontKernel.php';

$kernel = new FrontKernel('prod', false);
$kernel->handle()->send();
