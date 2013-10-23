# KnpBundles

Open-source code of the [knpbundles.com](http://knpbundles.com)
website, written in Symfony2.

Any ideas are welcome!

[![Build Status](https://secure.travis-ci.org/KnpLabs/KnpBundles.png)](http://travis-ci.org/KnpLabs/KnpBundles)

Please note that this service was previously called Symfony2Bundles but we had
to change the name due to [trademark issues](http://knplabs.com/blog/symfony2bundles-becomes-knpbundle).

## Install

### Get the code

    git clone git://github.com/KnpLabs/KnpBundles.git

### Configure

To configure your database in your development environment, copy
`/app/config/parameters.yml.dist` to `/app/config/parameters.yml` and
edit it according to your database settings.

If you want to use github connect locally, you'll need to
[create an app on github](https://github.com/settings/applications/new)
with the callback:

    http://yourlocalurl/login/check-github

And update the parameters.yml with the Client ID and Secret.

If you also want to use sensio labs connect locally, you'll need to
[create a client on sensio labs connect](https://connect.sensiolabs.com/account/app/new)
with the callback:

    http://yourlocalurl/login/check-sensio

And update the parameters.yml with the Client ID and Secret.

### Install vendors

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

#### Create database and tables

    php app/console doctrine:database:drop --force
    php app/console doctrine:database:create
    php app/console doctrine:schema:create

    php app/console --env=test doctrine:database:drop --force
    php app/console --env=test doctrine:database:create
    php app/console --env=test doctrine:schema:create

### Load data fixtures

    php app/console doctrine:fixtures:load
    php app/console --env=test doctrine:fixtures:load

### Run the tests (requires PHPUnit >= 3.5)

    phpunit -c app

#### Run the Behat tests (requires PHPUnit >= 3.5)

    ./bin/behat @KnpBundlesBundle

#### To generate migrations from your current schema

    php app/console doctrine:migrations:diff
    php app/console doctrine:migrations:migrate
    php app/console cache:warmup

## Usage

### Launch the consumer

We rely on RabbitMQ to update bundles:

* The main server **produces** messages saying "Hey, we should update this bundle"
* The **consumers** read these messages and update them

To launch a consumer, do:

    php app/console rabbitmq:consumer update_bundle

Note that you will need a functional rabbitmq server − Follow the instructions in [this page](http://www.rabbitmq.com/download.html) to install it.

### Populate document collections from GitHub

    php app/console kb:populate

This can take a long time. GitHub API is limited to 60 calls per minute,
so the commands needs to wait.

### Update all bundles in database

    php app/console kb:update:bundles

This can take a long time but should be run to trigger update on all bundles when this is needed.

### Search engine

We use [Solr](http://lucene.apache.org/solr/) and its PHP client [Solarium](http://solarium-project.org) to search bundles.

To install Solr, follow this steps:

1. Download the version 3.6.2 (new versions are not compatibles) and extract it
2. Uses the command `kb:solr:start --solr-path="/path/to/solar-3.6.2/example"`
3. And run `kb:solr:index --verbose`

Use the default Jetty server included with SOLR. The default directory used for the installation is `opt/solr/example`.
Copy the recommended configuration and schema found [**here**](https://github.com/KnpLabs/KnpBundles/blob/master/src/Knp/Bundle/KnpBundlesBundle/Resources/solr/conf/schema.xml)
to your solr/conf directory.

You can run SOLR using:

    php app/console kb:solr:start

See bin/prepare-test-solr.sh script
Bundles will be automatically indexed on next update, or you can force indexing by console command.

If you have Solr up and running, simply do:

    php app/console kb:solr:index --verbose

This will index all bundles.

### Generate sitemap

    php app/console kb:sitemap:generate --spaceless=1

Will create **sitemap.xml** and **sitemap.xml.gz** in web directory.
Sitemap includes bundles and user profiles
