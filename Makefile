PROJ := 'hpcloud-php'
SRCDIR := src
TESTS := test/Tests
VERSION := 'DEV'
DATE := `date "+%Y%m%d"`
GROUP := 'deprecated'

VFILES = src/HPCloud

docs :
	@cat ./config.doxy | sed 's/-UNSTABLE%/$(VERSION)/' | doxygen -

test :
	./vendor/bin/phpunit --color --exclude-group=deprecated $(TESTS);

fulltest:
	./vendor/bin/phpunit --color --exclude-group=deprecated --bootstrap=test/bootstrap_curl.php $(TESTS);
	./vendor/bin/phpunit --color --exclude-group=deprecated --bootstrap=test/bootstrap_phpstream.php $(TESTS)

test-cdn :
	php test/CDNTest.php

test-group :
	./vendor/bin/phpunit --color -v --group $(GROUP) $(TESTS)

test-verbose :
	./vendor/bin/phpunit --color -v  --exclude-group=deprecated $(TESTS) 2> curl-output.txt

lint : $(SRCDIR)
	find $(SRCDIR) -iname *.php -exec php -l {} ';'

dist: tar

tar: 
	@echo $(PROJ)-$(VERSION)-$(DATE).tgz
	# @tar -zcvf $(PROJ)-$(VERSION)-$(DATE).tgz $(SRCDIR)

.PHONY: docs test dist tar lint
