.PHONY: clean down start yarn_install end_alert logs init

clean:
	docker-compose down
	sudo rm -rf node_modules/

down:
	docker-compose down

start:
	docker-compose up -d webpack

yarn_install:
	yarn

end_alert:
	notify-send 'CRMWeb Web' 'Aplicação Iniciada'

logs:
	docker-compose logs -f

init: clean yarn_install start end_alert

mfe:
	docker-compose run --rm --service-ports  webpack sh -c 'yarn build-mfe'

mfe_prod:
	docker-compose run --rm --service-ports  webpack sh -c 'yarn build-mfe:prod'

analyzer:
	docker-compose run --rm -p 8888:8888 webpack sh -c 'yarn analyzer'

profile:
	docker-compose run --rm webpack sh -c 'yarn profile'

profile_mfe:
	docker-compose run --rm webpack sh -c 'yarn profile-mfe'