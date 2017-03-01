
NAME=sh_scoutnet_kalender
CURRENTVERSION=$(shell cat ext_emconf.php | grep "'version' =>" | cut -d "'" -f 4)
GIT_VERSION=$(shell git tag | sort | tail -n 1)
COMPOSER_VERSION=$(shell php -r "echo json_decode(file_get_contents('composer.json'))->version;")

default: zip

zip: Build/$(NAME)_$(CURRENTVERSION).zip

Build/%.zip: checkVersion
	git archive -o "Build/${NAME}_$(CURRENTVERSION).zip" $(CURRENTVERSION)

tag:
	@if [ ! -n "$$(git tag -l $(CURRENTVERSION))" ]; then git tag -a $(CURRENTVERSION) -m "Version $(CURRENTVERSION)"; fi

clean:
	rm -rf Build/*.zip

checkVersion:
	@echo GIT_VERSION: $(GIT_VERSION)
	@echo TYPO3_VERSION: $(CURRENTVERSION)
	@echo COMPOSER_VERSION: $(COMPOSER_VERSION)
	[ "$(GIT_VERSION)" = "$(CURRENTVERSION)" ]
	[ "$(GIT_VERSION)" = "$(COMPOSER_VERSION)" ]

