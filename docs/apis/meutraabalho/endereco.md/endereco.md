
### Solicitação de Alteração de Endereço

### Endpoint

> `{url_base}/solicitacoes/alteracoesenderecos`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| POST    | /                 |   Criação de uma Solicitação de Alteração de Dados Cadastrais           | object        |


### Request
```json

{
    "tiposolicitacao": "integer",
    "codigo": "integer",
    "justificativa": "string",
    "observacao": "string",
    "origem": "integer",
    "situacao": "integer",
    "logradouro": "string",
    "numero": "string",
    "complemento": "string",
    "cep": "string",
    "bairro": "string",
    "email": "string",
    "dddtel": "string",
    "telefone": "string",
    "dddcel": "string",
    "celular": "string",
    "paisresidencia": {
        "pais": "integer",
        "nome": "string",
    },
    "municipioresidencia": {
        "ibge": "integer",
        "nome": "string",
        "uf": "string",
    },
    "tipologradouro": {
        "tipologradouro": "string",
        "descricao": "string",
    }
}


```


### Response
#### Status: 200 - OK
```json

{
    "solicitacao": "guid",
    "trabalhador": "guid",
    "estabelecimento": "guid",
    "tiposolicitacao":"integer",
    "codigo": "integer",
    "justificativa":  "string",
    "observacao":  "string",
    "origem": "integer",
    "situacao":"integer",
    "created_at": "timestamp",
    "created_by": "json",
    "updated_at":  "timestamp",
    "updated_by": "json",
    "lastupdate":"timestamp",
    "tenant": "integer",
    "logradouro": "string",
    "numero": "string",
    "complemento": "string",
    "cep": "string",
    "bairro": "string",
    "email": "string",
    "dddtel": "string",
    "telefone": "string",
    "dddcel": "string",
    "celular": "string",
    "paisresidencia": {
        "pais": "integer",
        "nome": "string",
    },
    "municipioresidencia": {
        "ibge": "integer",
        "nome": "string",
        "uf": "string",
    },
    "tipologradouro": {
        "tipologradouro": "string",
        "descricao": "string",
    }
}

```

### Endpoint

> `{url_base}/solicitacoes/alteracoesenderecos/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| PUT     | /{id}             | Alteração de uma Solicitação de Alteração de Dados Cadastrais           | object        |

#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |



### Request
```json


{
    "solicitacao": "guid",
    "trabalhador": "guid",
    "estabelecimento": "guid",
    "tiposolicitacao":"integer",
    "codigo": "integer",
    "justificativa":  "string",
    "observacao":  "string",
    "origem": "integer",
    "situacao":"integer",
    "created_at": "timestamp",
    "created_by": "json",
    "updated_at":  "timestamp",
    "updated_by": "json",
    "lastupdate":"timestamp",
    "tenant": "integer",
    "logradouro": "string",
    "numero": "string",
    "complemento": "string",
    "cep": "string",
    "bairro": "string",
    "email": "string",
    "dddtel": "string",
    "telefone": "string",
    "dddcel": "string",
    "celular": "string",
    "paisresidencia": {
        "pais": "integer",
        "nome": "string",
    },
    "municipioresidencia": {
        "ibge": "integer",
        "nome": "string",
        "uf": "string",
    },
    "tipologradouro": {
        "tipologradouro": "string",
        "descricao": "string",
    }
}



```  


### Response
#### Status: 200 - OK
```json
{
}   

```

### Endpoint

> `{url_base}/solicitacoes/alteracoesenderecos/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| DELETE  | /{id}             |  Excluir uma Solicitação de Alteração de Dados Cadastrais               | object        |

#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |



### Response
#### Status: 200 - OK
```json
{
}   

```


### Endpoint

> `{url_base}/solicitacoes/alteracoesenderecos/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca de uma Solicitação de Alteração de Dados Cadastrais               | object        |

#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |



### Response
#### Status: 200 - OK
```json


{
    "solicitacao": "guid",
    "trabalhador": "guid",
    "estabelecimento": "guid",
    "tiposolicitacao":"integer",
    "codigo": "integer",
    "justificativa":  "string",
    "observacao":  "string",
    "origem": "integer",
    "situacao":"integer",
    "created_at": "timestamp",
    "created_by": "json",
    "updated_at":  "timestamp",
    "updated_by": "json",
    "lastupdate":"timestamp",
    "tenant": "integer",
    "logradouro": "string",
    "numero": "string",
    "complemento": "string",
    "cep": "string",
    "bairro": "string",
    "email": "string",
    "dddtel": "string",
    "telefone": "string",
    "dddcel": "string",
    "celular": "string",
    "paisresidencia": {
        "pais": "integer",
        "nome": "string",
    },
    "municipioresidencia": {
        "ibge": "integer",
        "nome": "string",
        "uf": "string",
    },
    "tipologradouro": {
        "tipologradouro": "string",
        "descricao": "string",
    }
}



```

### Endpoint

> `{url_base}/solicitacoes/alteracoesenderecos/`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 |  Busca de todas as Solicitações de Alteração de Dados Cadastrais        | object        |


### Response
#### Status: 200 - OK
```json
[
    {
        "solicitacao": "guid",
        "codigo": "string",
        "situacao": "integer",
        "created_at": "timestamp",
        "created_by": "json",
        "trabalhador": "guid",
        "estabelecimento": "guid"
    }
,(...)
]

```



| Origem                                  | Descrição                                          |
|-----------------------------------------|----------------------------------------------------|
| 1                                       | Meu RH                                             |
| 2                                       | Meu Trabalho                                       |

| Tipo da Solicitação                     | Descrição                                          |
|-----------------------------------------|----------------------------------------------------|
| 0                                       | Admissão Preliminar                                |
| 1                                       | Rescisão                                           |
| 2                                       | Adiantamento Avulso                                |
| 3                                       | VT Adicional                                       |
| 4                                       | Alteração de VT                                    |
| 5                                       | Alteração de Endereço                              |
| 6                                       | Falta                                              |
| 7                                       | Férias                                             |
| 8                                       | Salário sob demanda                                |
| 9                                       | Promoções                                          |
| 10                                      | Créditos e Descontos                               |

