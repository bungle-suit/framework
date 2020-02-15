.phony: test test-watch lint format regen-autoload

test:
	../vendor/bin/phpunit --bootstrap Tests/bootstrap.php Tests/

test-watch:
	noisy.py -d '.' -e .php -- '../vendor/bin/phpunit --bootstrap Tests/bootstrap.php Tests/'

lint:
	../vendor/bin/phpcs --standard=PSR2 --ignore=vendor .
	../vendor/bin/phpmd  . ansi ../.phpmd-ruleset.xml --exclude vendor

format:
	../vendor/bin/php-cs-fixer fix .
	../vendor/bin/phpcbf --standard=PSR2 --ignore=vendor .

regen-autoload:
	composer dump-autoload
