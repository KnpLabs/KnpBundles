<?php

$array = array();
foreach($repos as $repo) {
    $array[] = $bundle->toSmallArray();
}

echo json_encode($array);
