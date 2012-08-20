php app/console --env=test doctrine:database:drop --force
php app/console --env=test doctrine:database:create
php app/console --env=test doctrine:schema:create
php app/console --env=test doctrine:fixtures:load --no-interaction
