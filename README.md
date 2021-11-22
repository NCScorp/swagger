# Monitor Financeiro

Este projeto apresenta dados de contratos gerados pelo Diário Único.
<br>A estrutura do projeto foi criada a partir do projeto Serviços Front-end, onde foram removidos todos os modulos presentes na pasta **src/app/modules**

## Configuração
As configurações do sistema, que no momento se resume a url da api do back-end, se encontra em **src/config/config.json**.
<br>O ideal é que esse arquivo não seja persistido e somente seja montado no momento do deploy, de acordo com o ambiente.
<br>Este arquivo é utilizado principalmente pela pasta **src/app/core**, comum a alguns projetos como Serviços Front-end e Atendimento Comercial. Essa pasta visa unificar o processo inicial das aplicações, autenticação, busca dos arquivos de rotas das apis e o que mais vier a ser comum entre aplicações.
<br> Exemplo do arquivo de configuração
```json
{ 
    "api": {
        "url": "http://34.135.106.60/:tenant/diario_unico/"
    },
    "routing": { 
        "url": "https://api.dev.nasajonsistemas.com.br/fosrouting/js/routing"
    },
    "auth" : {
        "url": "https:\/\/auth.dev.nasajonsistemas.com.br\/auth",
        "realm":"DEV",
        "clientId":"monitor"
    }
}
```
Explicando o arquivo:
  - api > url: URL base utilizada na criação de rotas pelo serviço rotas.service.ts, que será apresentado num tópico abaixo. 
  - routing > url: URL utilizada para buscar rotas do back-end PHP/Symfony pela pasta **src/app/core**. Só será possível remover quando o código for refatorado para não buscar o profile da api neste endereço.
  - auth: Configuração passada diretamente para o Keycloak ao ser iniciado.

## Levantando ambiente
A aplicação vai ser levantada no endereço localhost:9000. A porta está configurada no arquivo **docker-compose.yml**, no serviço **webpack**.
  - Para instalar as dependências, rode:
  ``` make yarn_install```
  - Para levantar a aplicação, rode:
  ``` make start```
  - Para visualizar os logs, rode:
  ``` make logs```

## Telas do sistema
  - [Home](docs/home.md)
  - [Contratos](docs/contratos.md)

## Rotas para api do back-end.
As API's dos projetos em PHP/Symfony costumam disponibilizar uma url contendo todas as rotas do sistema, e o front-end utiliza uma biblioteca para montar as rotas.
<br>Como este não é o caso, foi necessario criar um service configurando todas as rotas a serem utilizadas pelo sistema.
<br>O service se encontra em **src/app/shared/rotas/rotas.service.ts**

### Configurando nova rota
Para configurar uma nova rota, no construtor do serviço de rotas adicione um novo item ao array **this.rotas**, exemplo:
<br>
```
this.rotas.push({
    nome: 'contratos_buscar',
    url: 'contratos/:id',
    urlParams: ['id']
});
```
<br> Neste exemplo, quando for gerar a url desta rota, deve-se passar o parametro "id" para ser substituido na url. Ao gerar a rota, parâmetros que não estejam no "urlParams" serão considerados query params.

### Gerando url a partir do nome da rota
Considere a api base **www.monitor.nasajon.com/**

#### Exemplo 1
Rota configurada como exemplo:
```
{
    nome: 'contratos_totais',
    url: 'contratos-estatisticas',
    urlParams: []
}
```
Seria possível gerar uma url com a seguinte chamada:
```
const url = this.rotasService.getRota('contratos_totais', { limit: 5 });
```
Isso geraria a rota: **www.monitor.nasajon.com/contratos-estatisticas?limit=5**

#### Exemplo 2
Rota configurada como exemplo:
```
{
    nome: 'contratos_listar',
    url: 'contratos',
    urlParams: []
}
```
Seria possível gerar uma url com a seguinte chamada:
```
const url = this.rotasService.getRota('contratos_listar', { expand: ['pessoa', 'itemContrato'] });
```
Isso geraria a rota: **www.monitor.nasajon.com/contratos?expand=pessoa&expand=itemContrato**


## Requisições HTTP
As requisições HTTP devem ser feitas a partir do serviço de api localizado em  **src/app/shared/api/api.service.ts**
<br>Caso o serviço não possua o método que você precisa, crie!