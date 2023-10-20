.PHONY: test build

all: test build

test:
	php test.php

build: 
	cd ../ && zip -r ~/wp-bfx-crypto-map.zip wp-bfx-crypto-map -x "wp-bfx-crypto-map/.git/*"

clean:
	rm -f ~/wp-bfx-crypto-map.zip
