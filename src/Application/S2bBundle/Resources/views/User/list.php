<?php $view->extend('S2bBundle::layout') ?>
<?php $view['main_menu']['Developers']->setIsCurrent(true) ?>
<?php $view['slots']->set('h1', '<span>'.count($users).'</span> Developers') ?>
<?php $view['slots']->set('title', 'All '.count($users).' Developers') ?>
<?php $view['slots']->set('slogan', 'List of Symfony2 Bundle developers') ?>
<?php $view['slots']->set('description', 'Symfony2 Bundle developers') ?>

<ul class="user-list clickable-list">
<?php foreach($users as $user): ?>
    <li class="item">
        <a class="item-link" href="<?php echo $view['router']->generate('user_show', array('name' => $user->getName())) ?>">
            <img class="gravatar" src="<?php echo $view['gravatar']->getUrl($user->getEmail(), 32, null, 'mm') ?>" width="32" height="32" />
            <?php echo $user->getName() ?>
            <?php if($user->getFullName()): ?>
                <span>(<?php echo $user->getFullName() ?>)</span>
            <?php endif; ?>
        </a>
        <?php if($user->hasBundles()): ?>
            <ul class="bundles">
            <?php foreach($user->getBundles() as $bundle): ?>
                <li>
                    <a href="<?php echo $view['router']->generate('repo_show', array('username' => $user->getName(), 'name' => $bundle->getName())) ?>">
                        <?php echo $bundle->getShortName() ?><span>Bundle</span><em><?php echo $bundle->getLastTagName() ?></em>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if($user->hasProjects()): ?>
            <ul class="projects">
            <?php foreach($user->getProjects() as $project): ?>
                <li>
                    <a href="<?php echo $view['router']->generate('repo_show', array('username' => $user->getName(), 'name' => $project->getName())) ?>">
                        <?php echo $project->getName() ?><em><?php echo $project->getLastTagName() ?></em>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php $nbBundles = $user->getNbContributionBundles(); $nbProjects = $user->getNbContributionProjects();
            ?><ul class="projects"><li><?php
            if($nbBundles || $nbProjects):
                ?>Contributes to <?php
                if($nbBundles):
                    echo $nbBundles ?> bundle<?php $nbBundles > 1 && print 's';
                    if($nbProjects) echo ' and ';
                endif;
                if($nbProjects):
                    echo $nbProjects ?> project<?php $nbProjects > 1 && print 's';
                endif;
            endif;
            ?></li></ul><?php
        ?>
    </li>
<?php endforeach; ?>
</ul>
