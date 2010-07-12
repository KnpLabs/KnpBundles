<?php

$array = array();
foreach($users as $user) {
    $array[] = $user->getRawValue()->toSmallArray();
}

echo json_encode($array);
