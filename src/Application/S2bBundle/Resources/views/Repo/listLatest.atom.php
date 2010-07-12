<?php print '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xml:lang="en-US">
    <id>Symfony2 Bundles &amp; Projects</id>
    <link type="text/html" rel="alternate" href="<?php echo $view->router->generate('bundle_list', array('sort' => 'createdAt'), true) ?>"/>
    <link type="application/atom+xml" rel="self" href="<?php echo $view->router->generate('latest', array('_format' => 'atom'), true) ?>"/>
    <title>Symfony2 Bundles &amp; Projects</title>
    <updated><?php echo date('c') ?></updated>
    <?php foreach($repos as $repo): ?>
    <entry>
        <id><?php echo $repo->getFullName() ?></id>
        <published><?php echo $repo->getCreatedAt()->format('c') ?></published>
        <updated><?php echo $repo->getLastCommitAt()->format('c') ?></updated>
        <link type="text/html" rel="alternate" href="<?php echo $view->router->generate('repo_show', array('username' => $repo->getUsername(), 'name' => $repo->getName()), true) ?>"/>
        <title><?php echo $repo->getName() ?></title>
        <author>
            <name><?php echo $repo->getUsername() ?></name>
            <uri>http://github.com/<?php echo $repo->getUsername() ?></uri>
        </author>
        <content type="html">
            <?php echo htmlspecialchars('<strong>'.$repo->getDescription().'</strong><br />') ?>
            <?php echo htmlspecialchars($view->markdown->transform($repo->getReadme())) ?>
        </content>
    </entry>
    <?php endforeach; ?>
</feed>
