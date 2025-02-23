# Setup Database

php bin/console doctrine:schema:update --force

## Migrate

php bin/console doctrine:migrations:migrate --no-interaction