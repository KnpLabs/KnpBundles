#symfony2bundles.org

Open-source code of the [symfony2bundles.org](http://symfony2bundles.org) website, written in Symfony2.

## Install

Symfony2Bundles require Symfony2 and MongoDB.

### Get the code

    git clone git://github.com/knplabs/symfony2bundles.git
    cd symfony2bundles
    git submodule update --init --recursive

The last command requires Git >= 1.6. Alternatively, you can run `git submodule init` and `git submodule update`, and recurse manually in submodules.

### Create Proxies dirs

    php s2b/console proxy:create
    php s2b/console-dev proxy:create

From now you can open index_dev.php on your browser, it should work.

### Patch annotation classes autoloading

As for now a bug prevents using annotation autoloading. Let's patch Symfony.

    cd src/vendor/Symfony/
    git apply ../../../autoloadAnnotation.patch
    cd ../../..

### Populate document collections from GitHub

    php s2b/console s2b:populate

This can take long time. GitHub API is limited to 60 calls per minute, so the commands needs to wait.

## Participating

Join the discussion on [Google Wave](https://wave.google.com/wave/waveref/googlewave.com/w+0CQKHWtqC).
