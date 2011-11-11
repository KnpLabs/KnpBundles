# bundles.knplabs.org

Open-source code of the [bundles.knplabs.org](http://bundles.knplabs.org)
website, written in Symfony2.

Any ideas are welcome!

Please note that this service was previously called Symfony2Bundles but we had
to change the name due to [trademark issues](http://knplabs.com/blog/symfony2bundles-becomes-knpbundle).


## Install

### Get the code

    git clone git://github.com/knplabs/knpbundles.git
    cd symfony2bundles
    git submodule update --init

The last command requires Git >= 1.6. Alternatively, you can run
`git submodule init` and `git submodule update`.

### Configure

To configure your database in your development environment, copy
`/app/config/parameters.yml.dist` to `/app/config/parameters.yml` and 
edit it according to your database settings.

#### Create database and tables

    php app/console doctrine:database:drop
    php app/console doctrine:database:create
    php app/console doctrine:schema:create

    php app/console --env=test doctrine:database:drop
    php app/console --env=test doctrine:database:create
    php app/console --env=test doctrine:schema:create

#### Load data fixtures

    php app/console doctrine:fixtures:load
    php app/console --env=test doctrine:fixtures:load

### Publish the assets

    php app/console assets:install --symlink web

#### Run the tests (requires PHPUnit >= 3.5)

    phpunit -c app

#### To generate migrations from your current schema

    php app/console doctrine:migrations:diff
    php app/console doctrine:migrations:migrate
    php app/console cache:warmup

#### Populate document collections from GitHub

    php app/console kb:populate

This can take long time. GitHub API is limited to 60 calls per minute,
so the commands needs to wait.
