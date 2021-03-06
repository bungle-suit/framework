.phony: test test-watch lint format regen-autoload test-cover

test:
	../vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/

test-watch:
	noisy.py -d '.' -e .php -- '../vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/'

test-watch-quiet:
	noisy.py -d '.' --vol 0.4 -e .php -- '../vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/'

test-cover:
	rm -rf /tmp/test-report/
	../vendor/bin/phpunit --bootstrap tests/bootstrap.php tests --coverage-html /tmp/test-report --whitelist .
	xdg-open /tmp/test-report/index.html

lint:
	../vendor/bin/phpcs --standard=PSR2 .
	../vendor/bin/phpmd  . ansi ../.phpmd-ruleset.xml --exclude vendor

format:
	../vendor/bin/php-cs-fixer fix
	../vendor/bin/phpcbf --standard=PSR2 --ignore=vendor .

regen-autoload:
	composer dump-autoload
