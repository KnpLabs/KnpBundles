<?php

$bundle = $bundle->getRawValue();

$array = array(
    'name' => $bundle->getName(),
    'username' => $bundle->getUsername(),
    'description' => $bundle->getDescription(),
    'score' => $bundle->getScore(),
    'followers' => $bundle->getFollowers(),
    'forks' => $bundle->getForks(),
    'createdAt' => $bundle->getCreatedAt()->getTimestamp(),
    'lastCommitAt' => $bundle->getLastCommitAt()->getTimestamp(),
    'tags' => $bundle->getTags(),
    'lastCommits' => $bundle->getLastCommits(),
    'readme' => $bundle->getReadme()
);

echo json_encode($array);
