<h3>Recent commits</h3>
<div class="sidemenu">
    <ol class="timeline">
    <?php foreach ($commits as $commit): ?>
        <li>
            <a href="<?php echo $view->router->generate('repo_show', array('username' => $commit['repo_username'], 'name' => $commit['repo_name'])) ?>">
                <?php echo $commit['repo_name'] ?>
            </a>
            <?php echo $commit['message'] ?><br />
            <span><?php echo $commit['author']['name'] ?> | <?php echo $view->time->ago(date_create($commit['committed_date'])) ?></span>
        </li>
    <?php endforeach; ?>
    </ol>
</div>
