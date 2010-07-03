<ol>
<?php foreach($bundles as $bundle): ?>
    <li>
        <a href="<?php echo $bundle->getGithubUrl() ?>"><?php echo $bundle->getFullName(); ?></a>
    </li>
<?php endforeach; ?>
</ol>
