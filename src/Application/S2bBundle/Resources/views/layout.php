<?php $view->stylesheets->add('bundles/s2b/css/reset.css') ?>
<?php $view->stylesheets->add('bundles/s2b/vendor/tipsy/stylesheets/tipsy.css') ?>
<?php $view->stylesheets->add('bundles/s2b/css/style.css') ?>
<?php $view->stylesheets->add('bundles/s2b/css/enhancements.css') ?>
<?php $view->javascripts->add('bundles/s2b/vendor/jquery.min.js') ?>
<?php $view->javascripts->add('bundles/s2b/vendor/tipsy/javascripts/jquery.tipsy.min.js') ?>
<?php $view->javascripts->add('bundles/s2b/js/ctrl.js') ?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript">var _sf_startpt=(new Date()).getTime()</script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php $view->slots->output('title') ?> | Symfony2 Bundles</title>
        <meta content="<?php $view->slots->output('description') ?>" name="description">
        <link rel="shortcut icon" href="/favicon.png" type="image/png" />
        <link href="<?php echo $view->router->generate('latest', array('_format' => 'xml')) ?>" rel="alternate" title="ATOM" type="application/atom+xml" />
        <?php echo $view->stylesheets ?>
    </head>
    <body>
        <!--header -->
        <div id="header-wrap">
            <div id="header">
                <a href="<?php echo $view->router->generate('homepage') ?>" id="logo">
                  <img alt="Symfony2Bundles" src="/bundles/s2b/images/bundle76.png" />
                </a>
                <h1>
                    <?php $view->slots->output('h1', 'Symfony<span>2</span> Bundles') ?>
                </h1>
                <p class="slogan">
                <?php $view->slots->output('slogan', 'Find the Bundles you need for your Symfony project!') ?>
                </p>

                <div id="nav">
                    <?php $view->output('S2bBundle:Main:menu', array('current' => $view->slots->get('current_menu_item', null))) ?>
                </div>

                <form id="quick-search" method="get" action="<?php echo $view->router->generate('search') ?>">
                    <fieldset class="search">
                        <label for="qsearch">Search:</label>
                        <input class="tbox" id="qsearch" type="text" name="q" value="<?php $view->slots->output('search_query', 'Search...') ?>" />
                        <button class="btn" title="Submit Search">Search</button>
                    </fieldset>
                </form>
            </div>
        </div>
        <!--/header-->

        <!-- content-outer -->
        <div id="content-wrap" class="clear" >
            <!-- content -->
            <div id="content">

                <!-- main -->
                <div id="main">
                    <?php $view->slots->output('_content') ?>
                </div>

                <!-- sidebar -->
                <div id="sidebar">
                    <?php $view->slots->output('sidemenu') ?>
                </div>
            </div>
        </div>

        <!-- footer-outer -->
        <div id="footer-outer" class="clear"><div id="footer-wrap">

                <div class="col-a">
                    <h3>Featured Bundles</h3>
                    <div class="footer-list">
                        <?php $view->actions->output('S2bBundle:Bundle:listBestScore') ?>
                    </div>
                </div>

                <div class="col-a">
                    <h3>Popular Bundles</h3>
                    <div class="footer-list">
                        <?php $view->actions->output('S2bBundle:Bundle:listPopular') ?>
                    </div>
                </div>

                <div class="col-a">
                    <h3>New Bundles</h3>
                    <div class="footer-list">
                        <?php $view->actions->output('S2bBundle:Bundle:listLastCreated') ?>
                    </div>
                </div>

                <div class="col-a">
                    <h3>Just updated</h3>
                    <div class="footer-list">
                        <?php $view->actions->output('S2bBundle:Bundle:listLastUpdated') ?>
                    </div>
                </div>

                <!-- /footer-outer -->
        </div></div>

        <!-- footer-bottom -->
        <div id="footer-bottom">

            <p class="bottom-left">
            Symfony2Bundles |
            Template by <a href="http://www.styleshout.com/">styleshout</a> |
            Supported by <a class="knplabs" href="http://www.knplabs.com/" title="French web agency who loves Symfony2"><img alt="knpLabs logo" src="<?php echo $view->assets->getUrl('bundles/s2b/images/knplabs.png') ?>" /></a>
            </p>

            <p class="bottom-right">
                <a href="<?php echo $view->router->generate('homepage') ?>">Home</a> |
                <a href="<?php echo $view->router->generate('latest', array('_format' => 'xml')) ?>">Syndication</a> |
                <a href="http://github.com/knplabs/symfony2bundles">Code</a> |
                <a href="http://symfony2bundles.uservoice.com/">Feedback</a> |
                <strong><a href="#header">Back to Top</a></strong>
            </p>

            <!-- /footer-bottom-->
        </div>
        <a class="fork_me" title="Fork <?php $view->slots->output('repo_name', 'symfony2bundles') ?> on GitHub" href="<?php $view->slots->output('repo_url', 'http://github.com/knplabs/symfony2bundles') ?>"><img src="http://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub" /></a>
        <?php echo $view->javascripts ?>
    </body>
</html>
