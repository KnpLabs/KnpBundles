<?php $view->extend('S2bBundle::layout') ?>

<?php $view->slots->set('h1', $user->getName()) ?>
<?php $view->slots->set('title', $user->getName().' - '.$user->getFullName()) ?>
<?php $view->slots->set('description', $user->getFullName().' has '.$user->getNbBundles().' Symfony2 bundles') ?>
<?php $view->slots->set('slogan', $user->getFullName() ? $user->getFullName() : ' ') ?>
<?php $view->slots->set('current_menu_item', 'user_list') ?>
<?php $view->slots->set('logo', '<img alt="'.$user->getName().'" src="'.$view->assets->getUrl(Bundle\GravatarBundle\Api::getUrl($user->getEmail('esc_raw'), array('size' => 80, 'default' => 'mm'))).'" width="80" height="80" />') ?>

<div class="post">

    <div class="right">
        <?php foreach(array('Bundle', 'Project') as $class): ?>
            <?php foreach(array('get', 'getContribution') as $method): ?>
                <?php $repos = $user->{$method.$class.'s'}() ?>
                <?php if($number = count($repos)): ?>
                    <h2 class="section-title">I <?php echo 'get' === $method ? 'manage' : 'contribute to' ?> <?php echo $number ?> <?php echo $class.($number > 1 ? 's' : '') ?></h2>
                    <?php $view->output('S2bBundle:'.$class.':bigList', array('repos' => $repos)) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <div class="left">

        <div class="post-meta">
            <h4>Infos</h4>
            <ul>
                <li class="time"><?php echo $view->time->ago($user->getLastCommitAt()->getRawValue()) ?></li>
                <li class="lego"><?php echo $user->getNbBundles() ?> Bundles</li>
                <li class="application"><?php echo $user->getNbProjects() ?> Projects</li>
                <?php if($user->getCompany()): ?>
                    <li class="company"><?php echo $user->getCompany() ?></li>
                <?php endif; ?>
                <?php if($user->getLocation()): ?>
                    <li class="location"><?php echo $user->getLocation() ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="post-meta">
            <h4>Links</h4>
            <ul>
                <li class="github"><a href="<?php echo $user->getGithubUrl() ?>">View on GitHub</a></li>
                <?php if($user->getBlog()): ?>
                    <li class="blog"><a href="<?php echo $user->getBlog() ?>">Blog</a></li>
                <?php endif; ?>
                <?php if($user->getEmail()): ?>
                    <li class="email"><a href="mailto:<?php echo $user->getObfuscatedEmail('esc_raw') ?>">Email</a></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

</div>

<?php $view->slots->start('sidemenu') ?>
<h3>Last commits</h3>
<div class="sidemenu">
    <ol class="timeline">
    <?php foreach ($user->getLastCommits() as $commit): ?>
        <li>
            <a href="<?php echo $view->router->generate('repo_show', array('username' => $commit['repo_username'], 'name' => $commit['repo_name'])) ?>">
                <?php echo $commit['repo_name'] ?>
            </a>
            <?php echo $commit['message'] ?><br />
            <span><?php echo $view->time->ago(date_create($commit['committed_date'])) ?></span>
        </li>
    <?php endforeach; ?>
    </ol>
</div>
<?php $view->slots->stop() ?>
