<?php

$array = array();
foreach($bundles as $bundle) {
    $array[] = $bundle->getRawValue()->toSmallArray();
}
echo json_encode($array);
