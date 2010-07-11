<?php

$array = array();
foreach($repo as $repo) {
    $array[] = $repo->getRawValue()->toSmallArray();
}

printf('%s(%s)', $callback, json_encode($array));
