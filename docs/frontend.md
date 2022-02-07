### Estrutura Frontend do Meu Trabalho
```html
|-- assets
  |-- [+] img
  |-- js
    |-- modules
      |-- Meurh
          |-- [+] Home
          |-- [+] Informesrendimentos
          |-- [+] Recibospagamentos
          |-- [+] Solicitacoessalariossobdemanda
          |-- index.html.twig
    |-- core
      |-- [-] authentication
		  |-- [-] keycloakAuth
          	   |-- keycloakAuth.service.ts
      |-- [+] mda
      |-- [+] mensagenserros
      |-- [+] interceptors
      |-- [+] services
		  |-- [-] inicializacao
          	   |-- inicializacao.service.ts

      |-- bootstrap-angular.ts
      |-- carregar-profile-tenant.ts
      |-- initialize-keycloak.ts
    |-- shared
         |-- [+] components
         |-- [+] directives
         |-- [+] services
         |-- [+] utils
    app.module.ts [main]
    config.routes.ts

  |-- sass
         |-- [+] templates
         |-- styles.scss
```


### Conteúdo dos arquivos:

`app.module.ts ->`Contém o controller principal da aplicação, chamado pelo ng-app. Ele é o responsável por carregar todos os outros módulos.

`keycloakAuth.service.ts ->` Métodos relacionados à autenticação no keycloak

`carregar-profile-tenant.ts ->` Recupera informações do tenant do sistema

`bootstrap-angular.ts ->` Responsável pelo carregamento manual da aplicação 

`initialize-keycloak.ts ->` Responsável por iniciar o keycloak e carregar o perfil do usuário antes de iniciar o aplicação

### Conteúdo das pastas

`modules ->` Diretório responsável por guardar as pastas sobrescritas do caso de uso. A partir do [PR 79](https://github.com/Nasajon/MeuTrabalho/pull/79), a sobrescrição é completa, deixando de estender os arquivos do MDA.

`core ->` Diretório responsável por guardar pastas e arquivos que são carregados na primeira inicialização do sistema, suas subpastas são:

```html

|-- authentication -> Aqui vão arquivos responsáveis pela autenticação do usuário ou no geral 

|-- guards -> [opcional] Aqui vão arquivos que são responsáveis por interceptar rotas ,tratamento de erros , autenticações etc

|-- http -> Classes cujos métodos que fazem requisições http (seria quase o equivalente aos nossos factories)

|-- interceptors -> Aqui vão todos os interceptors da aplicação

|-- mocks -> [opcional] -> usado para guardar os mocks usados na aplicação ou em testes.

|-- services -> Aqui vão serviços usados apenas vez ou no carregamento da aplicação como usuários e serviço de inicialização

```

`shared ->` Diretório onde vão todos os arquivos que podem ser exportados ou usados por outros módulos da aplicação, componentes ,diretivas e serviços compartilhadas.

```html

|-- components -> Componentes que podem ser reutilizados em toda a aplicação. 
|-- directives -> Diretivas que podem ser reutilizadas em toda a aplicação.
|-- pipes -> Filtros que podem ser reutilizados em toda a aplicação.
|-- services -> Services usados por mais de um caso de uso.
```