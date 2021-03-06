# This file is a generic Scoutnet Makefile file. The original is found in the dummy extension
# https://github.com/scoutnet/plugins.typo3.scoutnet_dummy/blob/master/Makefile
# MAKEFILE Version: 2.0.5

EXT_NAME=$(shell php -r "print(json_decode(file_get_contents('composer.json'), true)['extra']['typo3/cms']['extension-key']);")

EXT_VERSION=$(shell php -r '$$_EXTKEY = "${EXT_NAME}"; $$EM_CONF=[]; include("ext_emconf.php"); print($$EM_CONF[$$_EXTKEY]["version"]);')
GIT_VERSION=$(shell git tag | sort -V | tail -n 1)

NEXTPATCHVERSION=$(shell php -r 'list($$a,$$b,$$c) = (explode(".","$(EXT_VERSION)", 3)); echo "$$a.$$b.".($$c+1);')
NEXTMINORVERSION=$(shell php -r 'list($$a,$$b,$$c) = (explode(".","$(EXT_VERSION)", 3)); echo "$$a.".($$b+1).".0";')
NEXTMAJORVERSION=$(shell php -r 'list($$a,$$b,$$c) = (explode(".","$(EXT_VERSION)", 3)); echo ($$a + 1).".0.0";')

COMMIT_MESSAGE=$(shell git tag -l $(EXT_VERSION) -n99 | sed "s/^$(EXT_VERSION)[ ]*//g" | sed "s/^[ ]*//g" | sed -e ':a' -e 'N' -e '$$!ba' -e "s/\n/<br>/g")
INTERNAL=$(shell php -r '$$_EXTKEY = "${EXT_NAME}"; $$EM_CONF=[]; include("ext_emconf.php"); print($$EM_CONF[$$_EXTKEY]["internal"]);')

UNAME_S := $(shell uname -s)

ifeq ($(UNAME_S),Darwin)
# macos does not know how to handle getent
REPO_IP=131.188.205.253
WWW_IP=131.188.205.253
else
REPO_IP=$(shell getent hosts repo.scoutnet.de | awk '{ print $$1 }')
WWW_IP=$(shell getent hosts www.scoutnet.de | awk '{ print $$1 }')
endif

HAS_UNIT_TESTS=$(shell find Tests/Unit -name '*.php')
HAS_FUNCTIONAL_TESTS=$(shell find Tests/Functional -name '*.php')
HAS_ACCEPTANCE_TESTS=$(shell find Tests/Acceptance/Tests -name '*.php')


RELEASE_BUILD_FOLDER=.Build/Release
TEST_ROOT_FOLDER=.Build/Test

PHP_XDEBUG_ON ?= 0
PHP_XDEBUG_PORT ?= 9000
SCRIPT_VERBOSE ?= 0

PHP_VERSIONS ?= 7.3 7.4
TESTS ?= lint unit functional acceptance

COMPOSE_PROJECT_NAME_PREFIX=typo3-local-$(JOB_NAME)-$(BUILD_ID)-$(EXT_NAME)-

export GITHUB_USER=scoutnet
export GITHUB_REPO=plugins.typo3.$(EXT_NAME)

default: zip

zip: $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)_$(EXT_VERSION).zip

$(RELEASE_BUILD_FOLDER)/%.zip: checkVersion
	-@[ -d $(RELEASE_BUILD_FOLDER) ] || mkdir -p $(RELEASE_BUILD_FOLDER)
	git archive -o "$(RELEASE_BUILD_FOLDER)/$(EXT_NAME)_$(EXT_VERSION).zip" $(EXT_VERSION)

$(TEST_ROOT_FOLDER)/docker-%/.env:
	@echo "Generating env file for $*"
	-@[ -d $(TEST_ROOT_FOLDER)/docker-$* ] || mkdir -p $(TEST_ROOT_FOLDER)/docker-$*
	@echo "COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)$*" > $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "HOST_UID=`id -u`" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "HOST_HOME=$(HOME)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "ROOT_DIR=`pwd`" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "HOST_USER=$(USER)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "TEST_FILE=Web/typo3conf/ext/$(EXT_NAME)/Tests/$(shell php -r 'echo ucfirst("$(word 2,$(subst -, ,$*))");')" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "EXT_NAME=$(EXT_NAME)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "PHP_XDEBUG_ON=$(PHP_XDEBUG_ON)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "PHP_XDEBUG_PORT=$(PHP_XDEBUG_PORT)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "DOCKER_PHP_IMAGE=$(word 1,$(subst -, ,$*))" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "PHP_VERSION=$(word 1,$(subst -, ,$*))" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "EXTRA_TEST_OPTIONS=$(EXTRA_TEST_OPTIONS)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "SCRIPT_VERBOSE=$(SCRIPT_VERBOSE)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "PREFIX=$*" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "TYPO3_CONTEXT=Testing" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "REPO_IP=$(REPO_IP)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env
	@echo "WWW_IP=$(WWW_IP)" >> $(TEST_ROOT_FOLDER)/docker-$*/.env


# create .env for phpstorm debuger
.PHONY: phpStormDockerDotEnv
phpStormDockerDotEnv: Tests/Build/.env
Tests/Build/.env: $(TEST_ROOT_FOLDER)/docker-php74-phpStorm/.env
	@cp $(TEST_ROOT_FOLDER)/docker-php74-phpStorm/.env $@

$(TEST_ROOT_FOLDER)/docker-%/docker-compose.yaml:
	@echo "Generating docker-compose file for $*"
	-@[ -d $(TEST_ROOT_FOLDER)/docker-$* ] || mkdir -p $(TEST_ROOT_FOLDER)/docker-$*
	cp Tests/Build/docker-compose.yml $@

# TODO: replace the internal ip with the external if we do not run on jenkins (better replace with external in config and update if we run on jenkins)

stepPatchVersion:
	@cat ext_emconf.php | sed "s/'version' => '$(EXT_VERSION)'/'version' => '$(NEXTPATCHVERSION)'/g" > ext_emconf_new.php && mv ext_emconf_new.php ext_emconf.php
	@git add ext_emconf.php && git commit -m "new patch $(NEXTPATCHVERSION)"
	@echo "* Start Development of the new Version: $(NEXTPATCHVERSION). After feature freeze, use make tag to tag this release"

stepMinorVersion:
	@cat ext_emconf.php | sed "s/'version' => '$(EXT_VERSION)'/'version' => '$(NEXTMINORVERSION)'/g" > ext_emconf_new.php && mv ext_emconf_new.php ext_emconf.php
	@git add ext_emconf.php && git commit -m "new minor Version $(NEXTMINORVERSION)"
	@echo "* Start Development of the new Version: $(NEXTMINORVERSION). After feature freeze, use make tag to tag this release"

stepMajorVersion:
	@cat ext_emconf.php | sed "s/'version' => '$(EXT_VERSION)'/'version' => '$(NEXTMAJORVERSION)'/g" > ext_emconf_new.php && mv ext_emconf_new.php ext_emconf.php
	@git add ext_emconf.php && git commit -m "new Version $(NEXTMAJORVERSION)"
	@echo "* Start Development of the new Version: $(NEXTMAJORVERSION). After feature freeze, use make tag to tag this release"

tag:
	@if [ ! -n "$$(git tag -l $(EXT_VERSION))" ]; then git tag -a $(EXT_VERSION); fi
	@echo You can now use git push --tags to push all changes to github
	@echo To step development Version use make stepPatchVersion, make stepMinorVersion or make stepMajorVersion

release: checkVersion $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)_$(EXT_VERSION).zip
	@if [ -z "$(GITHUB_TOKEN)" ]; then echo "Please Set ENV GITHUB_TOKEN"; exit 2; fi
	@echo "* Upload Release $(EXT_VERSION) to Github"
	@github-release release -t $(EXT_VERSION) -d "Release of version $(EXT_VERSION)<br><br>$(COMMIT_MESSAGE)"
	@github-release upload -t $(EXT_VERSION) -f $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)_$(EXT_VERSION).zip -n "$(EXT_NAME)_$(EXT_VERSION).zip"
	@echo "* Upload Done"

.PHONY: clean
clean:
	-rm -rf $(RELEASE_BUILD_FOLDER)
	-rm -rf $(TEST_ROOT_FOLDER)
	-rm -rf .Build/
	-rm -f composer.lock
	-rm -rf Tests/Acceptance/Support/_generated
	-rm -rf Tests/Build/.env

.PHONY: cleanDocker
cleanDocker: $(TEST_ROOT_FOLDER)/docker-php74-composer/.env $(TEST_ROOT_FOLDER)/docker-php74-composer/docker-compose.yaml \
 $(TEST_ROOT_FOLDER)/docker-php73-lint/.env $(TEST_ROOT_FOLDER)/docker-php73-lint/docker-compose.yaml \
 $(TEST_ROOT_FOLDER)/docker-php73-unit/.env $(TEST_ROOT_FOLDER)/docker-php73-unit/docker-compose.yaml\
 $(TEST_ROOT_FOLDER)/docker-php73-functional/.env $(TEST_ROOT_FOLDER)/docker-php73-functional/docker-compose.yaml \
 $(TEST_ROOT_FOLDER)/docker-php73-acceptance/.env $(TEST_ROOT_FOLDER)/docker-php73-acceptance/docker-compose.yaml \
 $(TEST_ROOT_FOLDER)/docker-php74-lint/.env $(TEST_ROOT_FOLDER)/docker-php74-lint/docker-compose.yaml \
 $(TEST_ROOT_FOLDER)/docker-php74-unit/.env $(TEST_ROOT_FOLDER)/docker-php74-unit/docker-compose.yaml\
 $(TEST_ROOT_FOLDER)/docker-php74-functional/.env $(TEST_ROOT_FOLDER)/docker-php74-functional/docker-compose.yaml \
 $(TEST_ROOT_FOLDER)/docker-php74-acceptance/.env $(TEST_ROOT_FOLDER)/docker-php74-acceptance/docker-compose.yaml
	# Composer
	-cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_install; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_update; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_validate; docker-compose down
	# PHP 7.3
	-cd $(TEST_ROOT_FOLDER)/docker-php73-lint && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php73-lint; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php73-unit && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php73-unit; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php73-functional && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php73-functional_mariadb10; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php73-acceptance && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php73-acceptance_backend_mariadb10; docker-compose down
	# PHP 7.4
	-cd $(TEST_ROOT_FOLDER)/docker-php74-lint && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php74-lint; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php74-unit && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php74-unit; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php74-functional && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php74-functional_mariadb10; docker-compose down
	-cd $(TEST_ROOT_FOLDER)/docker-php74-acceptance && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php74-acceptance_backend_mariadb10; docker-compose down

checkVersion:
	@echo GIT_VERSION: $(GIT_VERSION)
	@echo TYPO3_VERSION: $(EXT_VERSION)
	@[ "$(GIT_VERSION)" = "$(EXT_VERSION)" ]
	@echo "* All Versions correct"

deploy: checkVersion $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)_$(EXT_VERSION).zip
ifeq ($(INTERNAL), 1)
	@echo "This Plugin is internaly only, we do not upload it to TER."
else
	# clean build folder
	-@[ -d $(RELEASE_BUILD_FOLDER)/$(EXT_NAME) ] && rm -rf $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)
	mkdir -p $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)
	unzip $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)_$(EXT_VERSION).zip -d $(RELEASE_BUILD_FOLDER)/$(EXT_NAME)
	# install ter uploader
	cd $(RELEASE_BUILD_FOLDER) && composer require namelesscoder/typo3-repository-client
	@cd $(RELEASE_BUILD_FOLDER) && vendor/bin/upload $(EXT_NAME) $(TYPO3_TER_USER) $(TYPO3_TER_PASSWORD) "$(shell git tag -l $(EXT_VERSION) -n99 | sed "s/^$(EXT_VERSION)[ ]*//g" | sed "s/^[ ]*//g")"
	@echo "uploaded $(EXT_VERSION)"
endif

.PHONY: init
init: clean composerInstall phpStormDockerDotEnv

.PHONY: composerInstall
composerInstall: $(TEST_ROOT_FOLDER)/docker-php74-composer/.env $(TEST_ROOT_FOLDER)/docker-php74-composer/docker-compose.yaml
	@mkdir -p /tmp/.composer
	@cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_install; docker-compose run composer_install
	@cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_install; docker-compose down
	# Create Link for functional tests to work properly they expect to have the vendor directory in the web directory
	@ln -fs ../vendor .Build/Web

.PHONY: composerUpdate
composerUpdate: $(TEST_ROOT_FOLDER)/docker-php74-composer/.env $(TEST_ROOT_FOLDER)/docker-php74-composer/docker-compose.yaml
	@mkdir -p /tmp/.composer
	@cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_update; docker-compose run composer_update
	@cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_update; docker-compose down

.PHONY: composerValidate
composerValidate: $(TEST_ROOT_FOLDER)/docker-php74-composer/.env $(TEST_ROOT_FOLDER)/docker-php74-composer/docker-compose.yaml
	@mkdir -p /tmp/.composer
	@cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_validate; docker-compose run composer_validate
	@cd $(TEST_ROOT_FOLDER)/docker-php74-composer && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)composer_validate; docker-compose down

AVAILABLE_TESTS = $(foreach test,$(TESTS),$(test)Test)

.PHONY: test
test: $(AVAILABLE_TESTS)
	@echo "done"

# Availbable Tests
.PHONY: $(AVAILABLE_TESTS)
$(AVAILABLE_TESTS): %Test: $(foreach ver,$(subst .,,$(PHP_VERSIONS)),%Test-php$(ver))

ALL_TESTS = $(foreach ver,$(subst .,,$(PHP_VERSIONS)),test-php$(ver))
.PHONY: $(ALL_TESTS)
$(ALL_TESTS): test-php%: lintTest-php% unitTest-php% functionalTest-php% acceptanceTest-php%


# Available PHPVersions

LINT_TESTS = $(foreach ver,$(subst .,,$(PHP_VERSIONS)),lintTest-php$(ver))
.PHONY: $(LINT_TESTS)
$(LINT_TESTS): lintTest-php%: $(TEST_ROOT_FOLDER)/docker-php%-lint/.env $(TEST_ROOT_FOLDER)/docker-php%-lint/docker-compose.yaml
	# Lint Test for php$*
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-lint && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-lint; docker-compose run lint
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-lint && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-lint; docker-compose down

UNIT_TESTS = $(foreach ver,$(subst .,,$(PHP_VERSIONS)),unitTest-php$(ver))
.PHONY: $(UNIT_TESTS)
$(UNIT_TESTS): unitTest-php%: $(TEST_ROOT_FOLDER)/docker-php%-unit/.env $(TEST_ROOT_FOLDER)/docker-php%-unit/docker-compose.yaml
ifneq ($(strip $(HAS_UNIT_TESTS)),)
	# Unit Test for php$*
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-unit && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-unit; docker-compose run unit
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-unit && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-unit; docker-compose down
endif

FUNCTIONAL_TESTS = $(foreach ver,$(subst .,,$(PHP_VERSIONS)),functionalTest-php$(ver))
.PHONY: $(FUNCTIONAL_TESTS)
$(FUNCTIONAL_TESTS): functionalTest-php%: $(TEST_ROOT_FOLDER)/docker-php%-functional/.env $(TEST_ROOT_FOLDER)/docker-php%-functional/docker-compose.yaml
ifneq ($(strip $(HAS_FUNCTIONAL_TESTS)),)
	# Functional Test for php$*
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-functional && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-functional_mariadb10; docker-compose run functional_mariadb10
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-functional && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-functional_mariadb10; docker-compose down
endif

# TODO: work with different dbms
#        case ${DBMS} in
#            mariadb)
#                docker-compose run functional_mariadb10
#            mssql)
#                docker-compose run functional_mssql2017cu9
#            postgres)
#                docker-compose run functional_postgres10
#            sqlite)
#                docker-compose run functional_sqlite

ACCEPTANCE_TESTS = $(foreach ver,$(subst .,,$(PHP_VERSIONS)),acceptanceTest-php$(ver))
.PHONY: $(ACCEPTANCE_TESTS)
$(ACCEPTANCE_TESTS): acceptanceTest-php%: $(TEST_ROOT_FOLDER)/docker-php%-acceptance/.env $(TEST_ROOT_FOLDER)/docker-php%-acceptance/docker-compose.yaml
ifneq ($(strip $(HAS_ACCEPTANCE_TESTS)),)
	# Acceptance Test for php$*
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-acceptance && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-acceptance_backend_mariadb10; docker-compose run acceptance_backend_mariadb10
	@cd $(TEST_ROOT_FOLDER)/docker-php$*-acceptance && export COMPOSE_PROJECT_NAME=$(COMPOSE_PROJECT_NAME_PREFIX)php$*-acceptance_backend_mariadb10; docker-compose down
endif





updateDockerImages:
	# pull typo3gmbh/phpXY:latest versions of those ones that exist locally
	@docker images typo3gmbh/php*:latest --format "{{.Repository}}:latest" | xargs -I {} docker pull {}
	# remove "dangling" typo3gmbh/phpXY images (those tagged as <none>)
	@docker images typo3gmbh/php* --filter "dangling=true" --format "{{.ID}}" | xargs -I {} docker rmi {}

