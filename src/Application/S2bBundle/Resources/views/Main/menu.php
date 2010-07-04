<ul>
<?php
$entries = array(
    'homepage' => 'Home',
    'all' => 'All Bundles',
    'search' => 'Search'
);

foreach($entries as $route => $text) {
    printf('<li class="%s"><a href="%s">%s</a></li>',
        $route == $current ? 'current' : '',
        $view->router->generate($route),
        $text
    );
}
?>
</ul>
