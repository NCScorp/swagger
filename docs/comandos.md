# Comandos importantes
[Voltar](../README.md)

Listar todas as rotas disponíveis:
``` bash
 docker exec -ti atendimento_app_1 app/console debug:route
```

Filtrar uma rota específica:
``` bash
 docker exec -ti atendimento_app_1 app/console debug:route | grep <descricao>
```

Executar os testes da aplicação:
``` bash
 docker exec atendimento_app_1 php vendor/codeception/codeception/codecept run
```

Abrir o POSTMAN com os dados da sua proxy:
``` bash
 postman --proxy-server=https://<user>:<password>@<ip>:<port>/
```