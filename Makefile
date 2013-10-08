APP=dead.phar
FILES=$(wildcard src/*)

all: dead.phar

dead.phar: $(FILES) clean composer
	php -d phar.readonly=false -r " \
	\$$phar = new Phar('dead.phar'); \
	\$$phar->buildFromDirectory('.','/src.*|vendor.*/'); \
	\$$phar->setStub('#!/usr/bin/env php'. PHP_EOL .\$$phar->createDefaultStub('src/loader.php'));"
	chmod +x dead.phar

clean:
	rm -f dead.phar

install:
	cp -f dead.phar /usr/lib
	ln -s /usr/lib/dead.phar /usr/bin/dead

deinstall:
	rm -f /usr/lib/dead.phar
	rm -f /usr/bin/dead

smoke: dead.phar
	./dead.phar

composer:
	./composer.phar install
