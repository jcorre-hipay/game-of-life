DOCKERFILE=./infra/Dockerfile
DOCKER_IMAGE=game-of-life
DOCKER_CONTAINER=game-of-life
DOCKER_USER=www-data
DOCKER_GROUP=www-data
APP_DIRECTORY=/var/www/game-of-life.net
APP_HTTP_PORT=3000

help:
	@echo ""
	@echo "Usage: make <action> [arguments]"
	@echo ""
	@echo "Actions:"
	@echo "  build            Builds the docker image of the application."
	@echo "  up               Runs a docker container from the built image."
	@echo "  down             Shuts down the docker container."
	@echo "  shell            Opens a shell on the docker container."
	@echo "  test             Runs all the tests of the application."
	@echo "  phpspec [args]   Runs the phpspec tests only."
	@echo "  phpunit [args]   Runs the phpunit tests only."
	@echo "  behat [args]     Runs the behat tests only."
	@echo "  composer <args>  Runs the composer command."
	@echo "  console <args>   Runs a command of the application."
	@echo "  help             Shows this help."
	@echo ""
	@echo "Parameters:"
	@echo "  dockerfile       $(DOCKERFILE)"
	@echo "  docker image     $(DOCKER_IMAGE)"
	@echo "  docker container $(DOCKER_CONTAINER)"
	@echo "  docker user      $(DOCKER_USER)"
	@echo "  docker group     $(DOCKER_GROUP)"
	@echo "  app directory    $(APP_DIRECTORY)"
	@echo "  app http port    $(APP_HTTP_PORT)"
	@echo ""

build:
	docker image build -t $(DOCKER_IMAGE) -f $(DOCKERFILE) .

up:
	docker container run -d --name $(DOCKER_CONTAINER) -p $(APP_HTTP_PORT):80 -v $(shell pwd):$(APP_DIRECTORY) $(DOCKER_IMAGE)

down:
	docker container stop $(DOCKER_CONTAINER)
	docker container rm $(DOCKER_CONTAINER)

test: phpspec phpunit behat
	@:

shell:
	docker container exec -it -u $(DOCKER_USER):$(DOCKER_GROUP) $(DOCKER_CONTAINER) /bin/bash

phpspec:
	docker container exec -u $(DOCKER_USER):$(DOCKER_GROUP) $(DOCKER_CONTAINER) ./vendor/bin/phpspec run $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

phpunit:
	docker container exec -u $(DOCKER_USER):$(DOCKER_GROUP) $(DOCKER_CONTAINER) ./vendor/bin/phpunit $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

behat:
	docker container exec -u $(DOCKER_USER):$(DOCKER_GROUP) $(DOCKER_CONTAINER) ./vendor/bin/behat $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

composer:
	docker container exec -u $(DOCKER_USER):$(DOCKER_GROUP) $(DOCKER_CONTAINER) composer $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

console:
	docker container exec -u $(DOCKER_USER):$(DOCKER_GROUP) $(DOCKER_CONTAINER) ./bin/console $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))

%:
	@:

.PHONY: help build up down test shell phpspec phpunit behat composer console
