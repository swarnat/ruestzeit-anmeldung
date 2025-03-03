# Setup Database

php bin/console doctrine:schema:update --force

## Migrate

php bin/console doctrine:migrations:migrate --no-interaction

## Compile Assets

php bin/console asset-map:compile

## Maintenance Mode

Create a file .maintenance in Root directory to disable the frontend functions completely.  
To allow single IPs to bypass the maintenance mode, just write this ip one per line into .maintenance file.  
You can also define a Cookie, called "maintenance_bypass" and set that to "1" to bypass maintenance mode.