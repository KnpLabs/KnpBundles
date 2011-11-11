php app/console --env=test doctrine:database:drop
php app/console --env=test doctrine:database:create
php app/console --env=test doctrine:schema:create
php app/console --env=test doctrine:fixtures:load
