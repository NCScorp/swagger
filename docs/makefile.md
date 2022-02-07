# Comandos

1. [Executar projeto](#run)
1. [Executar scripts de migração de banco](#migrate_migrations)
1. [Criar arquivo de migração de banco](#migrate_generate)
1. [Executar testes](#testes)


## 1. Preparar ambiente e executar a aplicação <a name="run"></a>

`$ make run -i`

> Em alguns momentos será preciso digitar a senha do seu usuário root do computador para realizar algumas ações que necessitam de permissões especiais. Permissões são necessárias quando os comandos de remover a pasta vendor, node_modules e dar permissão nas pastas de cache e logs forem executados, por exemplo.

Em alguns minutos a aplicação será inicializada. Recomenda-se esperar pelo final da execução dos testes (última etapa do run) para começar a utilizar a aplicação.

> -i é utilizado para não parar a execução do script em caso de falha. Ele será utilizado até as depreciações do MDA não gerarem excpetion.

## 2. Evoluir o banco através das migrações <a name="migrate_migrations"></a>

`$ make migrations`

## 3. Criar um arquivo de versionamento de banco (migração) <a name="migrate_generate"></a>

`$ make migration`

> Um arquivo foi gerado na pasta database/migrations. Lembre-se de colocar no comentário do cabeçalho da classe o número da tarefa e uma breve descrição do script.


## 4. Executar testes <a name="testes"></a>

`$ make tests`

> Esse passo sempre deve ser exeutado antes de finalizar a tarefa. 

> Após a execução pode ser que algumas tabelas tenham sido apagadas e seu conteúdo seja apenas o conteúdo do script de tests/_data/dump.sql . A propósito, é nesse dump que inicialmente deixamos alguns scripts iniciais da aplicação, para não precisar inserir os mesmos dados manualmente ao iniciar uma tarefa, entretanto são dados mais genéricos e se você estiver precisando construir um ambiente específico para algum cliente ou cenário, deve-se criar um arquivo sql e armazená-lo em outra pasta fora do teste.