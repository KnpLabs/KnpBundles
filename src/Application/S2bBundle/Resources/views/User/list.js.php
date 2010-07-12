<?php

$array = array();
foreach($users as $user) {
    $array[] = $user->getRawValue()->toSmallArray();
}

printf('%s(%s)', $callback, json_encode($array));
