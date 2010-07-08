<?php

$array = array();
foreach($bundles as $bundle) {
    $bundle = $bundle->getRawValue();
    $array[] = array(
        'name' => $bundle->getName(),
        'username' => $bundle->getUsername(),
        'description' => $bundle->getDescription(),
        'score' => $bundle->getScore(),
        'followers' => $bundle->getFollowers(),
        'forks' => $bundle->getForks(),
        'createdAt' => $bundle->getCreatedAt()->getTimestamp(),
        'lastCommitAt' => $bundle->getLastCommitAt()->getTimestamp(),
        'tags' => $bundle->getTags()
    );
}

printf('%s(%s)', $callback, json_encode($array));
