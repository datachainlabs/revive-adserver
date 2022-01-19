# COLORS
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)
GIT_BRANCH := $(shell git rev-parse --abbrev-ref HEAD)

# --------------- Maintenance --------------

.PHONY: add-remote-revive-source
## Adds the remote upstream revive-adserver repo so that changes can be synced
add-remote-revive:
	@echo "Registering upstream revive-adserver repo location"
	@git remote add upstream https://github.com/revive-adserver/revive-adserver.git
	@echo "visual check for remote source addition"
	@git remote -v


.PHONY: prep-build-env
prep-build-env:
	@echo "Preparing build/packaging environment for revive-adserver"
	@apt install unzip unrar p7zip-full -y
	@apt install openjdk-18-jdk -y
	@apt install ant -y
	@apt install php8.0 php8.0-cli -y
	@apt install php8.0-curl php8.0-intl php8.0-mbstring php8.0-mysqli php8.0-xml php8.0-xml php8.0-xsl -y
	@curl -sS https://getcomposer.org/installer -o composer-setup.php
	@HASH=`curl -sS https://composer.github.io/installer.sig`
	@php composer-setup.php --install-dir=/usr/local/bin --filename=composer
	@COMPOSER_ALLOW_SUPERUSER=1 composer update

.PHONY: sync-forked-upstream
## Pull the latest changes from the forked upstream master revive-adserver repo
sync-forked-revive:
	@echo "Syncing with forked upstream revive-adserver"
	@git checkout master
	@git fetch upstream
	@git merge upstream/master

.PHONY: build-revive
build-revive:
	@ant
	@mkdir -p ./revive-packages
	@mv /root/proving_grounds/revive-adserver/build/test-results/revive-adserver-*.tar.bz2 ./revive-packages/ 
	@mv /root/proving_grounds/revive-adserver/build/test-results/revive-adserver-*.tar.gz ./revive-packages/
	@mv /root/proving_grounds/revive-adserver/build/test-results/revive-adserver-*.zip ./revive-packages/
