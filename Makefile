
NAME=sh_scoutnet_webservice

default: build build/*.t3x

build/%.t3x:
	php bin/create_t3x.php src $(NAME) build/
