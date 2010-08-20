<?php $view->extend('S2bBundle::layout') ?>

<?php $view['slots']->set('h1', '404 - Page not found') ?>
<?php $view['slots']->set('slogan', 'There is nothing here.') ?>

<div style="text-align: center;">
    <img src="<?php echo $view['assets']->getUrl('bundles/s2b/images/404.jpg') ?>" alt="404 Not Found" />
</div>
