# Getting Started

## PHP Hosting

 - To setup ths application just clone the repository and configure your .env.local File
 - After setup the database setup/migrations is applied with the followiung command `php bin/console doctrine:migrations:migrate --no-interaction` 
 - Open /admin to login with the following credentials:

Username: ruestzeit-admin
Passwort: ruestzeit-admin

Please change this password directly after first login.

## Docker

Use the docker-compose.yaml from repository to run this project. It is not recommended for production, because passwords are easy to guess and mariadb port is available from host.  
  
Image: [swarnat/ruestzeit-anmeldung:latest](https://hub.docker.com/r/swarnat/ruestzeit-anmeldung)  

## Migrate

php bin/console doctrine:migrations:migrate --no-interaction

## Setup Database for development

php bin/console doctrine:schema:update --force

## Compile Assets

php bin/console asset-map:compile

## Maintenance Mode

Create a file .maintenance in Root directory to disable the frontend functions completely.  
To allow single IPs to bypass the maintenance mode, just write this ip one per line into .maintenance file.  
You can also define a Cookie, called "maintenance_bypass" and set that to "1" to bypass maintenance mode.