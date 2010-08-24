<ol>
<?php foreach($repos as $repo): ?>
    <li>
        <a title="<?php echo htmlspecialchars($repo->getDescription()) ?>" href="<?php echo $view['router']->generate('repo_show', array('username' => $repo->getUsername(), 'name' => $repo->getName())) ?>">
            <?php echo $repo->getName(); ?>
        </a>
    </li>
<?php endforeach; ?>
</ol>
