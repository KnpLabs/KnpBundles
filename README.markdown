#symfony2bundles.org

Open-source code of the [symfony2bundles.org](http://symfony2bundles.org) website, written in Symfony2.

## Install

### Get the code

    git clone git://github.com/knplabs/symfony2bundles.git
    cd symfony2bundles
    git submodule update --init --recursive

The last command requires Git >= 1.6. Alternatively, you can run `git submodule init` and `git submodule update`, and recurse manually in submodules.

### Configure

To configure your DB for your development and test environments, edit your `/s2b/config/config_dev_local.yml` and `/s2b/config/config_test_local.yml` to add your specific DB settings:

    imports:
      - { resource: config_dev.yml }

    doctrine.dbal:
      connections:
        default:
          driver:               PDOMySql
          dbname:               s2b
          user:                 root
          password:             changeme
          host:                 localhost
          port:                 ~

#### Create database and tables

    php s2b/console-dev doctrine:database:drop
    php s2b/console-dev doctrine:database:create
    php s2b/console-dev doctrine:schema:create

    php s2b/console-test doctrine:database:drop
    php s2b/console-test doctrine:database:create
    php s2b/console-test doctrine:schema:create

#### Generate the doctrine proxies

    php s2b/console-dev doctrine:generate:proxies
    php s2b/console-test doctrine:generate:proxies

#### Load data fixtures

    php s2b/console-dev doctrine:data:load
    php s2b/console-test doctrine:data:load

#### Run the tests

    phpunit -c s2b

#### To generate migrations from your current schema

    php s2b/console-dev doctrine:migrations:diff --bundle=Application\\S2bBundle
    php s2b/console-dev doctrine:migrations:migrate --bundle=Application\\S2bBundle
    php s2b/console-dev doctrine:generate:proxies

#### Populate document collections from GitHub

    php s2b/console s2b:populate

This can take long time. GitHub API is limited to 60 calls per minute, so the commands needs to wait.
