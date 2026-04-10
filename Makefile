IMAGE := dto-forge

UID = $(shell id -u)
GID = $(shell id -g)
export UID
export GID

# Цвета для вывода
GREEN = \033[0;32m
YELLOW = \033[0;33m
RED = \033[0;31m
NC = \033[0m # No Color

build:
	docker build \
	  --build-arg UID=$(UID) \
	  --build-arg GID=$(GID) \
	  -t $(IMAGE) .

install:
	docker run --rm -it \
	  -v $(PWD):/app \
	  $(IMAGE) composer install

update:
	docker run --rm -it \
	  -v $(PWD):/app \
	  $(IMAGE) composer update

# Известные группы
KNOWN_GROUPS=Validate Build DefaultGenerate
test-groups:
	@echo "$(GREEN)Доступные группы тестов:$(NC)"
	@for group in $(KNOWN_GROUPS); do echo "  - $$group"; done

test:
	@echo "$(GREEN)Запуск всех тестов...$(NC)"
	docker run --rm -it -v $(PWD):/app $(IMAGE) ./vendor/bin/pest

test-%: ## Запустить тесты переданной группы
	@if echo "$(KNOWN_GROUPS)" | grep -wq "$*"; then \
		echo "$(GREEN)Запуск тестов группы '$*'...$(NC)"; \
		docker run --rm -it -v $(PWD):/app $(IMAGE) ./vendor/bin/pest --group=$*; \
	else \
		echo "$(RED)Неизвестная группа '$*'.$(NC)"; \
		$(MAKE) test-groups; \
		exit 1; \
	fi

shell:
	docker run --rm -it -v $(PWD):/app $(IMAGE) bash

pint:
	docker run --rm -it -v $(PWD):/app $(IMAGE) ./vendor/bin/pint