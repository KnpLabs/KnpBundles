<?php

$user = $user->getRawValue();
$array = array(
    'name' => $user->getName(),
    'email' => $user->getEmail(),
    'fullName' => $user->getFullName(),
    'company' => $user->getCompany(),
    'location' => $user->getLocation(),
    'blog' => $user->getBlog(),
    'bundles' => $user->getBundleNames(),
    'lastCommitAt' => $user->getLastCommitAt()->getTimestamp(),
    'lastCommits' => $user->getLastCommits()
);

echo json_encode($array);
