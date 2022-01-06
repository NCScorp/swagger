# Evolução dos Scripts no Banco de Dados
[Voltar](../README.md)

Cada modificação no banco de dados deve ser feita utilizando o DoctrineMigrationsBundle.

## 1. Gerando a classe de migração

``` bash
 docker exec -it atendimento_app_1 php app/console doctrine:migrations:generate
```

Por padrão será utilizado o addSql, ao invés das facilidades oferecidas pelo Doctrine.  

```PHP
 $this->addSql("<script>"); 
```

> Obs 1: Cada addSql aceita apenas um comando SQL.
> Obs 2: Os scripts que possuem caracteres especiais que conflitam com o PHP deverão utilizar EOT ao invés de aspas, conforme o exemplo abaixo:

 ```PHP 
$this->addSql(<<<'EOT'
    script com caracteres especiais como " $ etc...
EOT
);
```  

> Note  que o EOT de fechamento de escopo deve estar em uma linha exclusiva sem identação.

## 2. Verificando as classes de migração existentes e quais foram executadas

``` bash
 docker exec -it atendimento_app_1 php app/console doctrine:migrations:status --show-versions
```

## 3. Migrando 

### 3.1 Executando um script

``` bash
docker exec -it atendimento_app_1 php app/console doctrine:migrations:migrate <número da versão do script>
```

### 3.2 Executando todos os scripts

``` bash
docker exec -it atendimento_app_1 php app/console doctrine:migrations:migrate
```

### 3.3 Retornando o banco para o seu estado inicial 

``` bash
docker exec -it atendimento_app_1 php app/console doctrine:migrations:migrate first
```

[Para mais informações sobre DoctrineMigrationsbundle clique aqui.](https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html)