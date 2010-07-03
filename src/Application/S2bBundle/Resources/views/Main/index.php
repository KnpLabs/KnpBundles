<?php $view->extend('S2bBundle::layout') ?>

<ul class="top_lists">
    <li>
        <h2>New Bundles</h2>
        <?php $view->actions->output('S2bBundle:Bundle:listLastCreated') ?>
    </li>
    <li>
        <h2>Just updated</h2>
        <?php $view->actions->output('S2bBundle:Bundle:listLastUpdated') ?>
    </li>
    <li>
        <h2>Popular Bundles</h2>
        <?php $view->actions->output('S2bBundle:Bundle:listPopular') ?>
    </li>
</ul>
