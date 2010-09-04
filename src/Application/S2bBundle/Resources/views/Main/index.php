<?php $view->extend('S2bBundle::layout') ?>

<?php $view['main_menu']['Home']->setIsCurrent(true) ?>
<?php $view['slots']->set('title', 'Home') ?>
<?php $view['slots']->set('description', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>
<?php $view['slots']->set('slogan', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>
<?php $view['slots']->set('sidemenu', $view['actions']->render('S2bBundle:Main:timeline')) ?>

<h2>Welcome to the community driven Symfony2 Bundles website!</h2>
<div class="home_text">
  <p>
  As for now, we index
    <a href="<?php echo $view['router']->generate('bundle_list') ?>"><strong><?php echo $nbBundles ?></strong> Bundles</a>,
    <a href="<?php echo $view['router']->generate('project_list') ?>"><strong><?php echo $nbProjects ?></strong> Projects</a> &amp
    <a href="<?php echo $view['router']->generate('user_list') ?>"><strong><?php echo $nbUsers ?></strong> Developers</a>.
  </p>
  <p>
  Symfony2Bundles is a spontaneous community initiative! Its purpose is to help us to find the best Bundles.
  </p>
  <p>
  It works by querying the GitHub API to discover new Bundles and update existing ones. Don't try to claim or describe your Bundles on this website! This task is done automatically. Just share your Bundle on GitHub and it will appear here in no time.
  </p>
  <p>
  Symfony2Bundles inspects Bundles and give them an internal ranking, based on GitHub followers and forks, the frequency of the commits, the quality of documentation and tests, and more to come. These criterions are mixed up and result in an internal rank we use to highlight the best Bundles. See the featured Bundles section above.
  </p>
  <p>
  Symfony2Bundles is Open Source! <a href="http://github.com/knplabs/symfony2bundles">Get the code</a> and contribute!
  This website is at early stages of development, and many things will change, especially the ranking algorithm:
  <pre>
    <?php echo $view['actions']->output('S2bBundle:Main:getRankCode', array('standalone' => true, 'ignore_errors' => true)); ?>
  </pre>
  </p>
<hr />
Are you a Bundle developer? You should read about <a title="On symfony-reloaded.org" href="http://symfony-reloaded.org/guides/Bundles/Best-Practices">Bundle Best Practices</a> and <a title="On symfony-reloaded.org" href="http://symfony-reloaded.org/guides/Bundles/Configuration">Bundle Configuration</a>.
</div>
