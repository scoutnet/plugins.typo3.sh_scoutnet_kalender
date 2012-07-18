
NAME=sh_scoutnet_kalender
CURRENTVERSION=$(shell cat src/ext_emconf.php | grep "'version' =>" | cut -d "'" -f 4)

default: build build/*.t3x

build:
	mkdir build

build/%.t3x:
	php bin/create_t3x.php src $(NAME) build/
