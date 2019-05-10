.PHONY: space deploy deploy_dev rm build app_key shell main_config update

UNAME := $(shell uname)

ifeq ($(UNAME), Darwin)
	DOCKER := docker
else
	DOCKER := sudo docker
endif

default: build

.env:
ifeq (,$(wildcard .env))
    $(shell cp .env.example .env)
    $(shell sudo chown 1000:1000 .env)
endif

include .env
export $(shell sed 's/=.*//' .env)

/etc/synchole:
	sudo install -d /etc/synchole

/etc/synchole/data: /etc/synchole
	sudo install -d -o 1000 -g 1000 /etc/synchole/data

/etc/synchole/acme.json: /etc/synchole
	sudo touch /etc/synchole/acme.json
	sudo chmod 600 /etc/synchole/acme.json

/etc/synchole/portainer: /etc/synchole
	sudo install -d -o 1000 -g 1000 /etc/synchole/portainer

space: /etc/synchole/data /etc/synchole/acme.json /etc/synchole/portainer

deploy: app_key main_config space
	$(DOCKER) stack deploy -c synchole_main.yml main

deploy_dev: app_key synchole_main_dev.yml space
	$(DOCKER) stack deploy -c synchole_main_dev.yml main

update:
	$(DOCKER) service update main_synchole --image synchole:latest --force

rm:
	$(DOCKER) stack rm main

build: app docker config
	$(DOCKER) build -t synchole .

main_config: .env
	$(DOCKER) run -it -v ${PWD}/.env:/app/synchole/.env synchole:latest bash -c "php artisan make:deploy-config" > synchole_main.yml || (cat synchole_main.yml; exit 1)

synchole_main_dev.yml: .env
	$(DOCKER) run -it -v ${PWD}/.env:/app/synchole/.env synchole:latest bash -c "php artisan make:deploy-config dev" > synchole_main_dev.yml || (cat synchole_main_dev.yml; exit 1)

app_key: .env
ifeq (, ${APP_KEY})
	@echo APP_KEY not found
	$(DOCKER) run -it -v ${PWD}/.env:/app/synchole/.env synchole:latest bash -c "php artisan key:generate"
endif


shell:
	$(DOCKER) exec -it `$(DOCKER) ps --filter "ancestor=synchole" --format "{{ .Names }}"` bash

