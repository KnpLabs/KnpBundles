<?php

require_once __DIR__.'/../front/S2bKernel.php';

$kernel = new S2bKernel('prod', false);
$kernel->handle()->send();
