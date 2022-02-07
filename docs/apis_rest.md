# Documentação das APIS rest do MeuTrabalho

Para todas APIS do Meu Trabalho é necessário enviar trabalhador e estabelecimento como construtores. 

## Meurh/Solicitacoesdocumentos

### Regra de Negócio

O banco de dados referente a todas solicitações herda diretamente a tabela meurh/solicitacoes. Isso nos permite ter uma PK única para todas solicitações. A partir dessa premissa, criamos uma tabela meurh/solicitacoesdocumentos que serve para anexar documentos de qualquer solicitação de forma centralizada em apenas uma tabela de banco.

É relevante saber que apenas solicitações com situação 0, isto é: abertas, podem ter documentos anexados ou removidos, caso tente-se inserir ou deletar qualquer documento em uma solicitação com uma situação diferente será retornado um erro.

### APIs Rest

| Função | Método | Nome | Caminho |
| --- | --- | --- | --- |
| **listar** | GET | meurh_solicitacoesdocumentos_listar | /{tenant}/solicitacoesdocumentos/{solicitacao}/listar |
 | | | | Onde {id} é o guid da solicitação no qual você deseja lista os documentos da solicitação |
 | **get** | GET | meurh_solicitacoesdocumentos_get | /{tenant}/solicitacoesdocumentos/{id} |
 | | | | Onde {id} é o guid da documento da solicitação |
 | **create** | POST | meurh_solicitacoesdocumentos_create | /{tenant}/solicitacoesdocumentos/ |
 | | | | Onde é necessário enviar no corpo da requisição: **solicitacao** (guid da solicitação pai do documento), **conteudo** (arquivo em base 64 - caso não seja PDF, é necessario enviar na mesma string de base64 o cabeçalho. Ex.: *data:application/pdf;base64,..*) e um objeto de tipodocumentocolaborador com o formato: "tipodocumentocolaborador": {"tipodocumentocolaborador": "guid"} |
 | **delete** | DELETE | meurh_solicitacoesdocumentos_delete | /{tenant}/solicitacoesdocumentos/{id} |
 | | | | Onde {id} é o guid da solicitacaodocumento no qual você deseja excluir |

 ## Persona/Trabalhadores

### Regra de Negócio

### APIs Rest

| Função | Método | Nome | Caminho |
| --- | --- | --- | --- |
| **Meus dados / VT** | GET | ns_trabalhadores_vt | /{tenant}/meusdados/vt |