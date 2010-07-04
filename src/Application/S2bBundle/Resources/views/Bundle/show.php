<?php $view->extend('S2bBundle::layout') ?>


<div class="post">

    <div class="right">
        <div class="post-install">
            <pre>git submodule add <?php echo $bundle->getGitUrl() ?> src/Bundle/<?php echo $bundle->getName() ?></pre>
        </div>

        <div class="markdown" >
            <?php echo $view->markdown->transform($bundle->getReadme()) ?>
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
            <ul class="tags">
                <li class="source"><a href="<?php echo $bundle->getGithubUrl() ?>">View source</a></li>
                <li class="download"><a href="<?php echo $bundle->getGithubUrl() ?>/tarball/master">Download</a></li>
            </ul>
        </div>

    </div>

</div>

<?php $view->slots->set('h1', $bundle->getShortName().'<span>Bundle</span>') ?>
<?php $view->slots->set('slogan', $bundle->getDescription()) ?>

<?php $view->slots->start('sidemenu') ?>
<div class="popular">
    <h3>Last commits</h3>
    <ul>
        <?php foreach($bundle->getLastCommits() as $commit): ?>
            <li>
                <a href="<?php echo $commit['url'] ?>"><?php echo $commit['message'] ?></a><br />
                <span>by <?php echo $commit['author']['name'] ?> on <?php echo date('Y-m-d', strtotime($commit['committed_date'])) ?></span>
            </li>
        <?php endforeach ?>
    </ul>
</div>
<?php $view->slots->stop() ?>
