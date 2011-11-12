php app/console --env=dev doctrine:database:drop --force
php app/console --env=dev doctrine:database:create
php app/console --env=dev doctrine:schema:create
php app/console --env=dev doctrine:fixtures:load
