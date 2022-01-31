.PHONY: run_fma	run final_test config_common composer_install yarn_install transform webpack tests database load_fma migrate clean down start_application end_alert

run_fma:	clean run load_fma end_alert

run_fma_light:	down transform  start_application migrate load_fma end_alert

run:	config_common composer_install transform migrate start_application end_alert

final_test:	clean run tests

config_common:
	touch common.env
	cp common.env.dist common.env

composer_install:
	composer install --ignore-platform-reqs --no-scripts

transform:
	docker-compose run --rm transformer
	
# webpack:
# 	docker exec -it crmweb_app_1  sh  -c "yarn run watch"

webpack:
	docker-compose run --rm webpack

tests:
	docker-compose run --rm app vendor/bin/codecept run --fail-fast

migrate:	database
	docker-compose up -d app
	@while true; do \
                if (docker-compose exec postgres bash -c 'psql -qAt -U postgres -d postgres -c "SELECT count(*) FROM pg_stat_activity where state IS NOT NULL" > /tmp/count && if grep -q "1" /tmp/count ;then true; else false; fi') then break; \
                else echo "postgres initial script still running..."; sleep 5; fi \
        done
	docker-compose exec app php app/console --no-interaction doctrine:migrations:migrate


database: 
	docker-compose up -d postgres
	@for i in `seq 1 5`; do \
                if (docker-compose exec postgres sh -c 'psql -U postgres -d postgres -c "select 1;"' 2>&1 > /dev/null) then break; \
                else echo "postgres initializing..."; sleep 5; fi \
        done

load_fma:	
	docker-compose exec postgres sh -c 'psql -U postgres -d integratto2 -f scripts/limpardadosglobais.sql -f scripts/fma.sql -1'

clean:
	docker-compose down
	rm -rf vendor/

down:
	docker-compose down

start_application:
	docker-compose up -d app
	# docker-compose exec app  sh  -c "apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.7/main/ nodejs=8.9.3-r1 && npm install --global yarn"

just_migrate:
	docker-compose exec app php app/console --no-interaction doctrine:migrations:migrate

end_alert:
	notify-send 'CRM Web' 'Aplicação Iniciada'

cache:
	sudo rm -rf var/cache/* && sudo rm -rf var/logs/* && sudo chmod 777 -R var