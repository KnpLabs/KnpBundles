<?php

$array = array();
foreach($repos as $repo) {
   $array[] = $repo->toSmallArray();
}

printf('%s(%s)', $callback, json_encode($array));
