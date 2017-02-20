
NAME=sh_scoutnet_kalender
CURRENTVERSION=$(shell cat ext_emconf.php | grep "'version' =>" | cut -d "'" -f 4)

default: build zip

zip: build/$(NAME)_$(CURRENTVERSION).zip

build:
	mkdir build

build/%.t3x:
	php bin/create_t3x.php src $(NAME) build/

build/%.zip:
	git archive -o "build/${NAME}_$(CURRENTVERSION).zip" $(CURRENTVERSION)

tag:
	@if [ ! -n "$$(git tag -l $(CURRENTVERSION))" ]; then git tag -a $(CURRENTVERSION) -m "Version $(CURRENTVERSION)"; fi

clean:
	rm -rf build
