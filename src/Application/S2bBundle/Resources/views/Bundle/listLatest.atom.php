<?php print '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xml:lang="en-US">
    <id>Symfony2 Bundles</id>
    <link type="text/html" rel="alternate" href="<?php echo $view->router->generate('bundle_list', array('sort' => 'createdAt'), true) ?>"/>
    <link type="application/atom+xml" rel="self" href="<?php echo $view->router->generate('latest', array('_format' => 'atom'), true) ?>"/>
    <title>Symfony2 Bundles</title>
    <updated><?php echo date('c') ?></updated>
    <?php foreach($bundles as $bundle): ?>
    <entry>
        <id><?php echo $bundle->getFullName() ?></id>
        <published><?php echo $bundle->getCreatedAt()->format('c') ?></published>
        <updated><?php echo $bundle->getLastCommitAt()->format('c') ?></updated>
        <link type="text/html" rel="alternate" href="<?php echo $view->router->generate('bundle_show', array('username' => $bundle->getUsername(), 'name' => $bundle->getName()), true) ?>"/>
        <title><?php echo $bundle->getName() ?></title>
        <author>
            <name><?php echo $bundle->getUsername() ?></name>
            <uri>http://github.com/<?php echo $bundle->getUsername() ?></uri>
        </author>
        <content type="html">
            <?php echo htmlspecialchars('<strong>'.$bundle->getDescription().'</strong><br />') ?>
            <?php echo htmlspecialchars($view->markdown->transform($bundle->getReadme())) ?>
        </content>
    </entry>
    <?php endforeach; ?>
</feed>
