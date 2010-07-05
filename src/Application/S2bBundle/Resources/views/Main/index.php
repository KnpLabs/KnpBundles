<?php $view->extend('S2bBundle::layout') ?>

<?php $view->slots->set('current_menu_item', 'homepage') ?>
<?php $view->slots->set('title', 'Home') ?>
<?php $view->slots->set('description', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>
<?php $view->slots->set('slogan', 'Comprehensive list of Symfony2 Bundles ordered by relevance and integrated with GitHub') ?>

<h2>Welcome to the community driven Symfony2 Bundles website!</h2>
<p>
As for now, <strong><?php echo $nbBundles ?></strong> Symfony2 Bundles are indexed. <a href="<?php echo $view->router->generate('all') ?>">View all bundles</a> or <a href="<?php echo $view->router->generate('search') ?>">search for bundles</a>!
</p>
<p>
Symfony2Bundles is a spontaneous community initiative! Its purpose is to help us to find the best Bundles.
</p>
<p>
It works by querying the GitHub API to discover new Bundles and update existing ones. Don't try to claim or describe your Bundles on this website! This task is done automatically. Just share your Bundle on GitHub and it will appear here in few time.
</p>
<p>
Symfony2Bundles inspects Bundles and give them an internal ranking, based on GitHub followers and forks, the frequency of the commits, the quality of documentation and tests, and more to come. These criterions are mixed up and result in an internal rank we use to highlight the best Bundles. See the featured Bundles section above.
</p>
<p>
This website is at early stages of development, and many things will change, especially the ranking algorithm.<br />
Symfony2Bundles is Open Source! <a href="http://github.com/knplabs/symfony2bundles">Get the code</a> and contribute!
</p>
