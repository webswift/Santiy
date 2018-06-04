#downloads -----------------------------------------

COMPOSER = composer.phar

$(COMPOSER):
	$(PHP) -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
	$(PHP) composer-setup.php
	$(PHP) -r "unlink('composer-setup.php');"

.PHONY: development_composer_packages

development_composer_packages: $(COMPOSER)
	$(PHP) $(COMPOSER) install

BOWER = node_modules/.bin/bower
GULP = node_modules/.bin/gulp

$(GULP):
	$(NPM) install

$(BOWER):
	$(NPM) install

#download-dependencies-local: $(MAILCATCHER) development_composer_packages $(BOWER) |  others_install
download-dependencies-local: development_composer_packages $(GULP)

install-dependencies-local: download-dependencies-local


.PHONY: others_install

others_install:
	$(GULP)

#$(BOWER) install

.PHONY: production_composer_packages

production_composer_packages: $(COMPOSER)
	$(PHP) $(COMPOSER) install --no-dev

production_node_packages:
	$(NPM) install --only=production

download-dependencies-production-local: production_composer_packages production_node_packages | others_install

install-dependencies-production-local: download-dependencies-production-local

.PHONY: rm_composer update_deps update_deps_production

rm_composer: 
	$(RM) -r composer.phar

update_deps:
	$(PHP) $(COMPOSER) update
	$(NPM) update
	

#$(GULP)
#$(BOWER) update

update-downloaded-dependencies-local: | rm_composer $(COMPOSER) update_deps

update_deps_production:
	$(PHP) $(COMPOSER) update --no-dev
	$(NPM) update --only=production
	$(BOWER) update
	$(GULP)


update-downloaded-dependencies-production-local: | rm_composer $(COMPOSER) update_deps_production

#codecept and generated files ------------------

CODECEPT = ./vendor/bin/codecept

$(CODECEPT) : $(COMPOSER)

check_SCRIPTS = tests/_support/_generated/AcceptanceTesterActions.php tests/_support/_generated/FunctionalTesterActions.php tests/_support/_generated/UnitTesterActions.php

$(check_SCRIPTS) : tests/*.yml codeception.yml
	$(CODECEPT) build

CLEANFILES = $(check_SCRIPTS)

#mailcatcher ----------------------------------

MAILCATCHER = vendor/ruby/bin/mailcatcher
MAILCATCHER_RUNNER = GEM_HOME=vendor/ruby $(MAILCATCHER)

$(MAILCATCHER) :
	$(GEM) install mailcatcher --no-format-executable --install-dir vendor/ruby --no-document

run-mailcatcher-local: $(MAILCATCHER)
	echo "Start mailcatcher"
	$(MAILCATCHER_RUNNER) --ip 127.0.0.1 --smtp-port 1025 --http-port 1080

stop-mailcatcher-local:
	echo "Stop mailcatcher"
	-$(CURL) -v -X DELETE http://127.0.0.1:1080


#----------------------------------------------
#probably TESTS
check-local: $(MAILCATCHER) $(check_SCRIPTS) check_functional

.PHONY: check_functional check-functional-local

check_functional: 
	$(CODECEPT) run functional

check-functional-local: $(MAILCATCHER) $(check_SCRIPTS) check_functional

#codecept acceptance testing ------------------

.PHONY: check_acceptance check-acceptance-local

check-acceptance-local: $(MAILCATCHER) $(check_SCRIPTS) check_acceptance

check_acceptance: 
	$(CODECEPT) run --env phantom acceptance

#clean --------------------------------------
clean-local:
	$(RM) -r composer.phar node_modules vendor


#merge-starterkit---------------------------

merge-starterkit-local: 
	$(GIT) fetch starter-kit
	$(GIT) merge starter-kit/master -m "merging starterkit updates"


#production-optimize ----------------------
ARTISAN = ./artisan

production-optimize-local:
	$(PHP) $(ARTISAN) view:clear
	$(PHP) $(ARTISAN) config:clear
	$(PHP) $(ARTISAN) route:clear
	$(PHP) $(ARTISAN) clear-compiled
	$(PHP) $(COMPOSER) dump-autoload --optimize
	$(PHP) $(ARTISAN) cache:clear
	$(PHP) $(ARTISAN) config:cache
	$(PHP) $(ARTISAN) route:cache
	$(PHP) $(ARTISAN) optimize

#gulp handlers------------------------------
gulp:
	$(GULP)

gulp-watch:
	$(GULP) watch


sos_push:
	git checkout master 
	git merge sos_polish 
	git push 
	git checkout sos_polish
