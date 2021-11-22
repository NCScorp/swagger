.PHONY: clean down start yarn_install end_alert logs init

clean:
	docker-compose down
	sudo rm -rf node_modules/

down:
	docker-compose down

config_json:
	touch src/config/config.json
	cp src/config/config.json.dist src/config/config.json

start:
	docker-compose up -d webpack

gerarprod:
	docker-compose up gerarprod

yarn_install:
	yarn

end_alert:
	notify-send 'Serviços Web' 'Aplicação Iniciada'

logs:
	docker-compose logs -f

init: clean config_json yarn_install start end_alert
