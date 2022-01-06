.PHONY: start transformer clear test_generate check_db_up assets test db_migrate composer_install yarn_install permissao owner
test:
	docker-compose run --rm app vendor/codeception/codeception/codecept run $(type) --fail-fast
generate_test:
	docker-compose run --rm app vendor/codeception/codeception/codecept generate:test $(name)
stop:
	docker-compose down
start:
	docker-compose up -d
transformer:
	docker-compose run --rm transformer
yarn_install:
	yarn
composer_install:
	composer install --ignore-platform-reqs --no-scripts
assets:
	docker-compose exec app app/console assets:install -v
	docker-compose exec app app/console assetic:dump -v
assets_watch:
	docker-compose exec app app/console assets:install
	docker-compose exec app app/console assetic:watch -v
assets_dump:
	docker-compose exec app app/console assetic:dump -v
	docker-compose exec app app/console assetic:watch -v
clear:
	sudo rm -rf app/cache/*
	sudo rm -rf app/logs/*
check_db_up: 
	@for i in `seq 1 5`; do \
		if (docker-compose exec postgres sh -c 'psql -U bancosweb -d integratto2 -c "select 1;"' 2>&1 > /dev/null) then break; \
		else echo "postgres initializing..."; sleep 5; fi \
	done
db_migrate:
	make check_db_up
	@while true; do \
		if (docker-compose exec postgres bash -c 'psql -qAt -U bancosweb -d integratto2	 -c "SELECT count(*) FROM pg_stat_activity where state IS NOT NULL" > /tmp/count && if grep -q "1" /tmp/count ;then true; else false; fi') then break; \
		else echo "postgres initial script still running..."; sleep 5; fi \
	done
	docker-compose exec app app/console doctrine:migration:migrate --no-interaction
db_generate:
	docker-compose exec app app/console doctrine:migration:generate
debug_route:
	docker-compose exec app app/console debug:route
permissao:
	sudo chmod -Rf 777 ${PWD}/app/cache/ ${PWD}/app/logs/
owner:
	sudo chown -Rf ${USER}:${USER} .
run:
	cp common.env.dist common.env
	make owner
	make composer_install
	make yarn_install -i
	cp app/config/parameters.yml.dist app/config/parameters.yml
	make transformer
	make start
	make db_migrate
	make permissao
	make owner
	make assets
	make test
init:
	make start
	make db_migrate
	make transformer
	make permissao
	make assets
	make test
