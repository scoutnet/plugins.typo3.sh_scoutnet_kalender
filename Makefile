
NAME=sh_scoutnet_kalender
CURRENTVERSION=$(shell cat ext_emconf.php | grep "'version' =>" | cut -d "'" -f 4)

default: zip

zip: Build/$(NAME)_$(CURRENTVERSION).zip

Build/%.zip:
	git archive -o "Build/${NAME}_$(CURRENTVERSION).zip" $(CURRENTVERSION)

tag:
	@if [ ! -n "$$(git tag -l $(CURRENTVERSION))" ]; then git tag -a $(CURRENTVERSION) -m "Version $(CURRENTVERSION)"; fi

clean:
	rm -rf Build/*.zip
