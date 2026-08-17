.PHONY: dev
dev:
	docker compose up -d --remove-orphans

.PHONY: shell
shell: dev
	docker compose exec app sh

.PHONY: ci
ci: dev
	docker compose exec app composer ci

.PHONY: php-8.3
php-8.3:
	docker compose down
	PHP_VERSION=8.3 docker compose build
	$(MAKE) dev
	docker compose exec app composer update
	docker compose exec app composer ci

.PHONY: php-8.4
php-8.4:
	docker compose down
	PHP_VERSION=8.4 docker compose build
	$(MAKE) dev
	docker compose exec app composer update
	docker compose exec app composer ci

.PHONY: php-8.5
php-8.5:
	docker compose down
	PHP_VERSION=8.5 docker compose build
	$(MAKE) dev
	docker compose exec app composer update
	docker compose exec app composer ci

.PHONY: docs-shell
docs-shell: dev
	docker compose exec pages sh

.PHONY: docs-build
docs-build: dev
	docker compose exec pages mdbook build