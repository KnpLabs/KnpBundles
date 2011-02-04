<?php

$array = array();
foreach($repos as $repo) {
    $array[] = $repo->toSmallArray();
}

echo json_encode($array);
