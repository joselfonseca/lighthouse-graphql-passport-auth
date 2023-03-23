test-all:
	@make test-php80-lowest
	@make test-php80-stable
	@make test-php81-lowest
	@make test-php81-stable
	@make test-php82-lowest
	@make test-php82-stable

test-php80-lowest:
	@docker-compose run --rm php80 sh -c '\
	composer update --prefer-lowest --prefer-dist -W && \
	php ./vendor/bin/phpunit'

test-php80-stable:
	@docker-compose run --rm php80 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	php ./vendor/bin/phpunit'

test-php81-lowest:
	@docker-compose run --rm php81 sh -c '\
	composer update --prefer-lowest --prefer-dist -W && \
	php ./vendor/bin/phpunit'

test-php81-stable:
	@docker-compose run --rm php81 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	php ./vendor/bin/phpunit'

test-php82-lowest:
	@docker-compose run --rm php82 sh -c '\
	composer update --prefer-lowest --prefer-dist -W && \
	php ./vendor/bin/phpunit'

test-php82-stable:
	@docker-compose run --rm php82 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	php ./vendor/bin/phpunit'