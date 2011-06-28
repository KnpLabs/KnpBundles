#symfony2bundles.org

Open-source code of the [symfony2bundles.org](http://symfony2bundles.org)
website, written in Symfony2.

## Install

### Get the code

    git clone git://github.com/knplabs/symfony2bundles.git
    cd symfony2bundles
    git submodule update --init

The last command requires Git >= 1.6. Alternatively, you can run
`git submodule init` and `git submodule update`.

### Configure

To configure your DB for your development and test environments, edit your
`/app/config/config_dev_local.yml` and `/app/config/config_test_local.yml`
to add your specific DB settings:

    imports:
      - { resource: config_dev.yml }

    doctrine.dbal:
      connections:
        default:
          driver:               PDOMySql
          dbname:               app
          user:                 root
          password:             changeme
          host:                 localhost
          port:                 ~

#### Create database and tables

    php app/console doctrine:database:drop
    php app/console doctrine:database:create
    php app/console doctrine:schema:create

    php app/console --env=test doctrine:database:drop
    php app/console --env=test doctrine:database:create
    php app/console --env=test doctrine:schema:create

#### Generate the doctrine proxies

    php app/console cache:warmup
    php app/console --env=test cache:warmup

#### Load data fixtures

    php app/console doctrine:fixtures:load
    php app/console --env=test doctrine:fixtures:load

#### Run the tests (requires latest PHPUnit 3.5)

    phpunit -c app

#### To generate migrations from your current schema

    php app/console doctrine:migrations:diff
    php app/console doctrine:migrations:migrate
    php app/console cache:warmup

#### Populate document collections from GitHub

    php app/console s2b:populate

This can take long time. GitHub API is limited to 60 calls per minute,
so the commands needs to wait.
