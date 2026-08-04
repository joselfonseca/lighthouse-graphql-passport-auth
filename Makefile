test-all:
	@make test-php82-stable
	@make test-php83-stable
	@make test-php84-stable
	@make test-php85-stable

# Passport 13 validates key file permissions, which git does not carry through
# a checkout, so the test keys are restricted before the suite runs.
test-php82-stable:
	@docker compose run --rm php82 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	chmod 600 tests/storage/oauth-private.key tests/storage/oauth-public.key && \
	php ./vendor/bin/phpunit'

test-php83-stable:
	@docker compose run --rm php83 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	chmod 600 tests/storage/oauth-private.key tests/storage/oauth-public.key && \
	php ./vendor/bin/phpunit'

test-php84-stable:
	@docker compose run --rm php84 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	chmod 600 tests/storage/oauth-private.key tests/storage/oauth-public.key && \
	php ./vendor/bin/phpunit'

test-php85-stable:
	@docker compose run --rm php85 sh -c '\
	composer update --prefer-stable --prefer-dist -W && \
	chmod 600 tests/storage/oauth-private.key tests/storage/oauth-public.key && \
	php ./vendor/bin/phpunit'
