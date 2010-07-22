<?php $view->extend('S2bBundle::layout') ?>

<?php $view->slots->set('title', $repo->getName().' by '.$repo->getUsername()) ?>
<?php $view->slots->set('description', $repo->getDescription()) ?>
<?php $view->slots->set('slogan', $repo->getDescription()) ?>
<?php $view->slots->set('repo_name', $repo->getName()) ?>
<?php $view->slots->set('repo_url', $repo->getGitHubUrl()) ?>

<div class="post">

    <div class="right">
        <div class="post-install">
            <pre><?php $view->slots->output('git_command') ?></pre>
        </div>

        <div class="markdown">
            <?php echo $view->markdown->transform($repo->getReadme('esc_raw')) ?>
        </div>

    </div>

    <div class="left">

        <div class="post-meta">
            <h4>Infos</h4>
            <ul>
                <li class="user"><a href="<?php echo $view->router->generate('user_show', array('name' => $repo->getUsername())) ?>"><?php echo $repo->getUsername() ?></a></li>
                <li class="time"><?php echo $view->time->ago($repo->getLastCommitAt()->getRawValue()) ?></li>
                <li class="watch"><?php echo $repo->getNbFollowers() ?> followers</li>
                <li class="fork"><?php echo $repo->getNbForks() ?> forks</li>
            </ul>
        </div>

        <div class="post-meta">
            <h4>Links</h4>
            <ul>
                <li class="github"><a href="<?php echo $repo->getGithubUrl() ?>">View source</a></li>
                <li class="download"><a href="<?php echo $repo->getGithubUrl() ?>/tarball/master">Download</a></li>
                <?php if($repo->getHomepage()): ?>
                <li class="homepage"><a href="<?php echo $repo->getHomepage() ?>">Homepage</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <?php if(count($repo->getContributors())): ?>
        <div class="post-meta">
            <h4>Contributors</h4>
            <ul>
                <?php foreach($repo->getContributors() as $contributor): ?>
                <li class="user"><a href="<?php echo $view->router->generate('user_show', array('name' => $contributor->getName())) ?>"><?php echo $contributor->getName() ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="post-meta">
            <h4>Versions</h4>
            <?php if(count($repo->getTags())): ?>
            <ul>
                <?php foreach($repo->getTags() as $tag): ?>
                <li class="version"><a href="<?php echo $repo->getGithubUrl() ?>/tree/<?php echo $tag ?>"><?php echo $tag ?></a></li>
                <?php endforeach ?>
            </ul>
            <?php else: ?>
            No version released.
            <?php endif; ?>
        </div>

    </div>

</div>

<?php $view->slots->start('sidemenu') ?>
<h3>Last commits</h3>
<div class="sidemenu">
    <ul>
        <?php foreach($repo->getLastCommits() as $commit): ?>
        <li>
        <a href="<?php echo $commit['url'] ?>"><?php echo strtok($commit['message'], "\n\r"); ?></a><br />
        <span><?php echo $commit['author']['name'] ?> | <?php echo $view->time->ago(date_create($commit['committed_date'])) ?></span>
        </li>
        <?php endforeach ?>
    </ul>
</div>
<?php $view->slots->stop() ?>
