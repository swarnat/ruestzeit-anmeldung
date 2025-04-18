start:
	symfony server:start -d
	
stop:
	symfony server:stop

build:
	npm run build

dev:
	npm run watch
	
clear-cache:
	php bin/console cache:clear

docker-build:
	docker build . -t swarnat/ruestzeit-anmeldung:latest
	
docker-push:
	docker push swarnat/ruestzeit-anmeldung:latest