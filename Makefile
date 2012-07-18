
NAME=sh_scoutnet_kalender

default: build build/*.t3x

build:
	mkdir build

build/%.t3x:
	php bin/create_t3x.php src $(NAME) build/
