.PHONY: test build

all: test build

test:
	./vendor/bin/phpunit test.php

build: 
	cd ../ && zip -r ~/bfx-wp-crypto-map.zip bfx-wp-crypto-map -x "bfx-wp-crypto-map/.git/*"

clean:
	rm -f ~/bfx-wp-crypto-map.zip
