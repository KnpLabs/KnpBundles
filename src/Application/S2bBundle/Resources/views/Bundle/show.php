<?php $view->extend('S2bBundle::layout') ?>

<?php $view->slots->set('h1', $bundle->getShortName().'<span>Bundle</span>') ?>
<?php $view->slots->set('title', $bundle->getName().' by '.$bundle->getUsername()) ?>
<?php $view->slots->set('description', $bundle->getDescription()) ?>
<?php $view->slots->set('slogan', $bundle->getDescription()) ?>
<?php $view->slots->set('repo_name', $bundle->getName()) ?>
<?php $view->slots->set('repo_url', $bundle->getGitHubUrl()) ?>
<?php $view->slots->set('current_menu_item', 'all') ?>

<div class="post">

    <div class="right">
        <div class="post-install">
            <pre>git submodule add <?php echo $bundle->getGitUrl() ?> src/Bundle/<?php echo $bundle->getName() ?></pre>
        </div>

        <div class="markdown">
            <?php echo $view->markdown->transform($bundle->getReadme('esc_raw')) ?>
        </div>

    </div>

    <div class="left">

        <div class="post-meta">
            <h4>Infos</h4>
            <ul>
                <li class="user"><a href="<?php echo $bundle->getUsernameUrl() ?>"><?php echo $bundle->getUsername() ?></a></li>
                <li class="time"><?php echo $bundle->getDaysSinceLastCommit() ?> days ago</li>
                <li class="watch"><?php echo $bundle->getFollowers() ?> followers</li>
                <li class="fork"><?php echo $bundle->getForks() ?> forks</li>
            </ul>
        </div>

        <div class="post-meta">
            <h4>Links</h4>
            <ul>
                <li class="source"><a href="<?php echo $bundle->getGithubUrl() ?>">View source</a></li>
                <li class="download"><a href="<?php echo $bundle->getGithubUrl() ?>/tarball/master">Download</a></li>
            </ul>
        </div>

        <div class="post-meta">
            <h4>Versions</h4>
            <?php if(count($bundle->getTags())): ?>
                <ul>
                    <?php foreach($bundle->getTags() as $tag): ?>
                        <li class="version"><a href="<?php echo $bundle->getGithubUrl() ?>/tree/<?php echo $tag ?>"><?php echo $tag ?></a></li>
                    <?php endforeach ?>
                </ul>
            <?php else: ?>
                No version released.
            <?php endif; ?>
        </div>

    </div>

</div>

<?php $view->slots->start('sidemenu') ?>
<div class="sidemenu">
    <h3>Last commits</h3>
    <ul>
        <?php foreach($commits as $commit): ?>
            <li>
                <a href="<?php echo $commit['url'] ?>"><?php echo $commit['message'] ?></a><br />
                <span><?php echo $commit['author']['name'] ?> | <?php echo $view->time->ago(date_create($commit['committed_date'])) ?></span>
            </li>
        <?php endforeach ?>
    </ul>
</div>
<?php $view->slots->stop() ?>
