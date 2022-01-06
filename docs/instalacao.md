# Como Iniciar o Projeto
[Voltar](../README.md)

Os projetos da Nasajon utilizam o Docker por padrão, e em sua maioria já possuem todas configurações necessárias para que o ambiente docker seja executado. Os passos descritos abaixo levam em consideração que você já possua o docker e docker-composer instalados.

Para iniciar o projeto, basta seguir os passos abaixo:

Clone o projeto para a sua máquina:

``` bash
 git clone git@github.com:Nasajon/Atendimento
```

Entre na pasta da aplicação

``` bash
 cd Atendimento
```

Inicie os serviços

``` bash
 docker-compose up -d
```

O projeto utiliza o composer para gestão de dependências e nós utilizamos o composer como um container docker. Para saber mais sobre como funciona, você pode ver um exemplo no nosso repositorio [Dockerfiles](https://github.com/Nasajon/Dockerfiles/tree/master/bin "Dockerfiles")

Com o composer disponível, execute o seguinte comando para instalar as bibliotecas:
``` bash
 composer install
```

Instale também os pacotes do yarn:
``` bash
 yarn install
```

E os comandos abaixo para instalar/compilar os assets da aplicação
``` bash
 docker exec -ti atendimento_app_1 app/console assetic:install
 docker exec -ti atendimento_app_1 app/console assetic:watch
```

Para iniciar o banco, é necessário executar as migrations do Doctrine
``` bash
docker exec -it atendimento_app_1 php app/console doctrine:migrations:migrate
```