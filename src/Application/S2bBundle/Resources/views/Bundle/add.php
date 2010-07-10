<h3>Add a Bundle</h3>
Are we missing a Bundle?<br />
You can point it out here:
<form class="add_bundle" action="<?php echo $view->router->generate('bundle_add') ?>" method="POST">
    <input class="hint" value="http://github.com/xxx/yyyBundle" name="url" />
</form>
