

### Documentos para uma Solicitação
### Endpoint

> `{url_base}/solicitacoes/<tipo>/<id>/documentos`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 |  Retorna os Documentos de uma solicitação                               | object        |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |
| tipo                                               | String do tipo de solicitação                                          |

| tipo                                    | descrição                                          |
|-----------------------------------------|----------------------------------------------------|
| admissoes                               | admissão preliminar                                |
| alteracoesenderecos                     | alteração de dados cadastrais                      |
| faltas                                  | falta                                              |
| ferias                                  | férias                                             |



### Response
#### Status: 200 - OK
```json
[
    {
        "solicitacaodocumento": "guid",
        "solicitacao": "guid",
        "caminhodocumento": "string",
        "created_by": "json",
        "created_at": "timestamp",
        "tenant": "integer",
        "tipodocumentocolaborador": {
            "tipodocumentocolaborador": "guid",
            "descricao": "string",
            "tenant": "integer"
        }
    },
    (...)
]
```




### Endpoint

> `{url_base}/solicitacoes/<tipo>/<id>/documentos`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| POST    | /                 |  Salvar Documento para uma solicitação                                  | object        |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |
| tipo                                               | String do tipo de solicitação                                          |

| tipo                                    | descrição                                          |
|-----------------------------------------|----------------------------------------------------|
| admissoes                               | admissão preliminar                                |
| alteracoesenderecos                     | alteração de dados cadastrais                      |
| faltas                                  | falta                                              |


###  Parâmetros do form


| Atributo                                                  | Tipo                | Obs.                       |
|-----------------------------------------------------------|---------------------|-----------------------------
| form[trabalhador]                                         | "guid"              |                            |
| form[estabelecimento]                                     | "guid"              |                            |
| form[tipodocumentocolaborador][tipodocumentocolaborador]  | "guid"              |                            |
| form[solicitacao]                                         | "guid"              |                            |
| form[conteudo]                                            | "file"              |    (application/pdf)       |



### Response
#### Status: 200 - OK
```json
{
    "solicitacaodocumento": "guid",
    "solicitacao": "guid",
    "caminhodocumento": "string",
    "created_by": "json",
    "created_at": "timestamp",
    "tenant": "integer",
    "tipodocumentocolaborador": {
        "tipodocumentocolaborador": "guid",
        "descricao": "string",
        "tenant": "integer"
    }
}
```


### Endpoint


> `{url_base}/solicitacoes/<tipo>/<id>/documentos/<solicitacaodocumento>`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| DELETE  | /                 |  Excluir Documento para uma solicitação                                 | object        |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |
| tipo                                               | String do tipo de solicitação                                          |
| solicitacaodocumento                               | Identificador do documento                                             |

| tipo                                    | descrição                                          |
|-----------------------------------------|----------------------------------------------------|
| admissoes                               | admissão preliminar                                |
| alteracoesenderecos                     | alteração de dados cadastrais                      |
| faltas                                  | falta                                              |



### Response
#### Status: 200 - OK
```json
{
}
```


### Tipos de Documentos


### Endpoint

> `{url_base}/{tiposolicitacao}/tiposdocumentoscolaboradores/`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca os Tipos de Documentos para uma solicitação de um colaborador     | object        |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| estabelecimento                                    | Identificador do estabelecimento                                       |
| trabalhador                                        | Identificador do colaborador                                           |
| tiposolicitacao                                    | Tipo da solicitação                                                    |

| tipo                                      |
|-------------------------------------------|
| admissão - 0                              |
| rescisão - 1                              |
| adiantamento avulso - 2                   |
| vt adicional - 3                          |
| alteração de vt - 4                       |
| alteração de endereço - 5                 |
| falta - 6                                 |
| férias - 7                                |
| salaŕio sob demanda - 8                   |
| promoções - 9                             |


### Response
#### Status: 200 - OK
```json
[
    {
        "tipodocumentorequerido": "guid",
        "tiposolicitacao": "integer",
        "obrigatorio": "boolean",
        "tenant": "integer",
        "tipodocumentocolaborador": {
            "tipodocumentocolaborador": "guid",
            "descricao": "string",
            "tenant": "integer"
        }
    },
    (...)
]
```

### Endpoint

> `{url_base}/tiposdocumentoscolaboradores/configuracoes`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca os Tipos de Documentos para um colaborador                        | object        |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| estabelecimento                                    | Identificador do estabelecimento                                       |
| trabalhador                                        | Identificador do colaborador                                           |

### Response
#### Status: 200 - OK
```json
[
    {
        "tipodocumentocolaborador": "guid",
        "descricao": "string",
        "tenant": "integer"
    },
,(...)
]
```
