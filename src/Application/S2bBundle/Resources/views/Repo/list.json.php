<?php

$array = array();
foreach($repos as $repo) {
    $array[] = $repo->getRawValue()->toSmallArray();
}

echo json_encode($array);
