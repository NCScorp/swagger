.PHONY: config_common composer start migrations transform down tests run clean database end_alert

config_common:
	touch app/config/parameters.yml
	touch common.env
	cp app/config/parameters.yml.dist app/config/parameters.yml
	cp common.env.dist common.env
	mkdir -p var/logs var/sessions var/cache/dev && touch var/logs/dev.log && (chmod 777 -Rf var/logs var/sessions var/cache || true) && (chown -Rf www-data:www-data var/logs var/sessions var/cache || true)

composer:
	composer install --ignore-platform-reqs --no-scripts

permitions:
	sudo chmod -R 777 ./var

start:
	docker-compose up -d

migrations: 
	docker-compose run --rm app bin/console doctrine:migrations:migrate --no-interaction 

transform:
	docker-compose run --rm -e SYMFONY_DEPRECATIONS_HELPER=disabled transformer

clean: down
	rm -rf var/cache/
	rm -rf vendor/

down:
	docker-compose down

generate_migration:
	docker-compose exec app php bin/console doctrine:migrations:generate

database: 
	docker-compose up -d postgres
	@while true; do \
                if (docker-compose exec postgres bash -c 'psql -qAt -U postgres -d postgres -c "SELECT count(*) FROM pg_stat_activity where state IS NOT NULL" > /tmp/count && if grep -q "1" /tmp/count ;then true; else false; fi') then break; \
                else echo "postgres initial script still running..."; sleep 5; fi \
        done

tests:
	docker-compose run --rm -e SYMFONY_DEPRECATIONS_HELPER=disabled app vendor/bin/codecept run --fail-fast

end_alert:
	notify-send 'Meu Trabalho' 'Aplicação Iniciada'

run: config_common composer transform database migrations tests start end_alert  


bash:
	docker-compose run --rm app sh
