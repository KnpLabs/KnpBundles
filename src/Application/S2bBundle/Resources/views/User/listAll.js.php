<?php

$array = array();
foreach($users as $user) {
    $user = $user->getRawValue();
    $array[] = array(
        'name' => $user->getName(),
        'email' => $user->getEmail(),
        'fullName' => $user->getFullName(),
        'company' => $user->getCompany(),
        'location' => $user->getLocation(),
        'blog' => $user->getBlog(),
        'bundles' => $user->getBundleNames()
    );
}

printf('%s(%s)', $callback, json_encode($array));
