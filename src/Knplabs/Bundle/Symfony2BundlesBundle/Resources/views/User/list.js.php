<?php

$array = array();
foreach($users as $user) {
    $array[] = $user->toSmallArray();
}

printf('%s(%s)', $callback, json_encode($array));
