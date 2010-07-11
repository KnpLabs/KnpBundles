<?php

$array = array();
foreach($bundles as $bundle) {
    $array[] = $bundle->getRawValue()->toSmallArray();
}

printf('%s(%s)', $callback, json_encode($array));
