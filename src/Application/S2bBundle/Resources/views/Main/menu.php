<ul>
<?php
$entries = array(
    'homepage' => 'Home',
    'bundle_list' => 'Bundles',
    'user_list' => 'Developers',
    'search' => 'Search'
);

foreach($entries as $route => $text) {
    printf('<li class="%s"><a href="%s">%s</a></li>', $route == $current ? 'current' : '', $view->router->generate($route), $text);
}
?>
<li>
    <a title="Symfony2 Bundles feed" href="<?php echo $view->router->generate('latest', array('_format' => 'atom')) ?>">
        <img src="<?php echo $view->assets->getUrl('bundles/s2b/images/feed16.png') ?>" />
    </a>
</li>
</ul>
