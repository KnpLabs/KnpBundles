<?php

$array = array();
foreach($repos as $repo) {
    $array[] = $repo->getRawValue()->toSmallArray();
}

printf('%s(%s)', $callback, json_encode($array));
