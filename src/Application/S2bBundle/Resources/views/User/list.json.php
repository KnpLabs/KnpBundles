<?php

$array = array();
foreach($users as $user) {
    $array[] = $user->toSmallArray();
}

echo json_encode($array);
