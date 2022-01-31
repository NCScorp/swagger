## Instalação

#### Inicializando a aplicação

##### 1. Clone o projeto
```$ git clone git@github.com:Nasajon/CRMWeb.git```

Acesse a pasta do projeto

```$ cd CRMWeb/```

#### Utilizando Make (Ubuntu)

##### 1. Execute o comando make:

```$ make run ```

> Caso o composer e o yarn não estejam instalados em sua máquina, utilize as imagens docker apropriada

A aplicação está pronta para ser acessada no seu navegador.


##### Para popular o sistema com dados da FMA:

###### Executa apenas o script de banco

```$ make load_fma```

###### Sobe todo o projeto e no final executa o script de banco

```$ make run_fma```

##### Antes de abrir o Pull Request de uma tarefa execute o teste completo do sistema:

```$ make final_test```


#### Reexecutando MDA

Se for necessário reexecutar o MDA (transformer + webpack) utilize o seguinte comando:

```$ make transformer ```  

Se for necessário reexecutar o MDA (webpack) utilize o seguinte comando:

```$ make webpack ```  


#### Problemas e soluções com o make:


##### 1. Problema ao executar comandos envolvendo o yarn, em específico "permissão negada para acessar .yarnrc"

Para resolver esse problema:

1. Crie o arquivo yarnrc (pode criar na pasta sugerida pelo yarn) - não coloque o arquivo na pasta do projeto

2. No comando para executar o container do yarn, acrescente um volume apontando para o arquivo .yarnrc criado e dê permissão de leitura escrita (rw). O arquivo do yarn se encontra em /usr/local/bin/.yarn 


---------------------------------------------------------------------------------------------------------------------------

#### Tradicional

##### 1. Execute o composer install
```$ composer install --ignore-platform-reqs```

> Caso o composer não esteja instalado em sua máquina, utilize uma imagem docker apropriada.
> Obs: caso haja falha por causa de requisitos execute: composer install --ignore-platform-reqs 

##### 2. Execute yarn install
``` $ yarn ```

##### 3. Peça o common.env para um desevolvedor ativo do projeto OU Copie o common.env.dist para common.env e edite-o

``` $ cp common.env.dist common.env```
> Configure o arquivo com as alterações, quando necessárias, para seu ambiente

##### 4. Inicie os containers

``` $ docker-compose up -d ```

Antes de prosseguir, espere o banco terminar a execução dos scripts iniciais. Para verificar se ele terminou pode exibir o log dos containers e verificar se os scripts foram executados.

```$ docker-compose logs -f```

Obs: para encerrar a exibição do log, basta teclar ctrl+c.

##### 5. Execute o migrate do Doctrine no container do Symfony

```$ docker-compose exec app app/console doctrine:migrations:migrate```

##### 6. Para popular o banco criado no passo anterior escolha uma das seguinte opções:

* Excute um script SQL para popular, que respeite a estrutura do banco **ou**
* Execute os testes no container do Symfony através do comando:
```$ docker-compose exec app vendor/bin/codecept run --fail-fast```
> Obs: Se a segunda maneira for escolhida, o banco será populado com os mesmos dados utilizados nos testes.

A aplicação está pronta para ser acessada no seu navegador.

#### Reexecutando MDA

Se for necessário reexecutar o MDA utilize o seguinte comando:

```$ docker-compose up transformer```  

---

# Troca de _branch_

Ao trocar de branches, recriar a aplicação com final_test (afim de realizar os testes para verificar integridade da branch).

```$ git checkout {branch}```

```$ make final_test```

#### Integração com ERP Api

As rotas do sistema estão sendo utilizadas pela api do CRM Web e pela api do ERP Api. Por isso, é necessário subir os dois projetos para o funcionamento correto da aplicação.

1. Siga o passo a passo para levantar o projeto do CRM Web.
2. Busque o IP do serviço de postgres. Para isso, inspecione o serviço e busque o gateway.

```$ docker inspect back_postgres_1 ```

3. No projeto ERP Api:
    - 3.1 No arquivo app_dev.php, antes da criação da instancia do Kernel, adicionar o seguinte código para previnir erros de cors nas requisições OPTIONS:
    ```
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
        die();
    }
    ```
    - 3.2 No arquivo docker-compose.yml, comentar serviço de postgres.
    - 3.3 No arquivo docker-compose.yml, configurar o serviço 'app' para utilizar outra porta para refletir a porta 80, pois já está em uso pelo CRM Web.
    - 3.4 Utilizar a mesma porta do serviço 'app' do item 3.3 na configuração de rotas do ERP Api no CRM Web, localizada em front/config/rotasSistema.json, propriedade 'urlErpapi'. Segue exemplo caso a porta utilizada seja 81:
    ```
    {
        "url": "http://localhost/fosrouting/js/routing.json",
        "urlErpapi": "http://localhost:81/fosrouting/js/routing.json"
    }
    ```
    - 3.5 Ter certeza que os dois sistemas estão utilizando as mesmas configurações de autenticação de keycloak. Isso pode ser verificado comparando as variáveis de ambiente:
        - keycloak_realm
        - keycloak_url
        - keycloak_client_id
    - 3.6 Ajuste a variável de ambiente 'database_host' para utilizar o ip gateway do serviço 'postgres' do CRM Web, buscado no item 2.
    - 3.7 Subir projeto do ERP Api

[Voltar a página principal](../../README.md)
