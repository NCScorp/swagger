
### Solicitação de Alteração de Vt

### Endpoint

> `{url_base}/solicitacoes/alteracoes-vts`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| POST    | /                 |  Criação da Solicitação de Alteração de Vt                              | object        |


### Request
```json

{
    "solicitacoesalteracoesvtstarifas": [
        {
            "trabalhador": "guid",
            "quantidade": "integer",
            "tarifaconcessionariavt": {
                "tarifaconcessionariavt": "guid",
                "codigo": "string",
                "descricao": "string",
                "tipo": "integer",
                "valor": "string",
                "valorformatado": "string",
            },
        }
    ],
    "motivo": "string"
}

```


### Response
#### Status: 200 - OK
```json
{
    "solicitacao":  "guid",
    "estabelecimento":  "guid",
    "tiposolicitacao": "integer",
    "codigo": "integer",
    "motivo": "string",
    "observacao": "string",
    "origem": "integer",
    "situacao": "integer",
    "created_at": "timestamp",
    "created_by": "json",
    "updated_at": "timestamp",
    "updated_by": "json",
    "lastupdate": "timestamp",
    "tenant": "integer",
    "trabalhador": {
        "trabalhador":  "guid",
        "agencia":  "guid",
        "numerocontasalario": "string",
        "numerocontasalariodv": "string",
        "salarioliquidoestimado": "string",
        "estabelecimento":  "guid"
    }
}
```

### Endpoint

> `{url_base}/solicitacoes/alteracoes-vts/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| PUT     | /{id}             | Alteração da Solicitação de Alteração de Vt                             | object        |

#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |



### Request
```json
{
    "solicitacao":  "guid",
    "estabelecimento":  "guid",
    "tiposolicitacao": "integer",
    "codigo": "integer",
    "motivo": "string",
    "observacao": "string",
    "origem": "integer",
    "situacao": "integer",
    "created_at": "timestamp",
    "created_by": "json",
    "updated_at": "timestamp",
    "updated_by": "json",
    "lastupdate": "timestamp",
    "tenant": "integer",
    "trabalhador": {
        "trabalhador":  "guid",
        "agencia":  "guid",
        "numerocontasalario": "string",
        "numerocontasalariodv":"string",
        "salarioliquidoestimado": "string",
        "estabelecimento":  "guid"
    },
    "solicitacoesalteracoesvtstarifas": [
        {
            "solicitacaoalteracaovttarifa": "guid",
            "quantidade": "integer",
            "tarifaconcessionariavt": {
                "tarifaconcessionariavt":  "guid",
                "codigo": "string",
                "descricao":"string",
                "tipo": "integer",
                "valor": "string",
                "valorformatado": "string"
            }
        }
    ]
}

```  


### Response
#### Status: 200 - OK
```json
{
}   

```

### Endpoint

> `{url_base}/solicitacoes/alteracoes-vts/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| DELETE  | /{id}             |  Exclusão da Solicitação de Alteração de Vt                             | object        |

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

> `{url_base}/solicitacoes/alteracoes-vts/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 |  Busca de uma da Solicitação de Alteração de Vt                         | object        |

#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |



### Response
#### Status: 200 - OK
```json

{
    "solicitacao":  "guid",
    "estabelecimento":  "guid",
    "tiposolicitacao": "integer",
    "codigo": "integer",
    "motivo": "string",
    "observacao": "string",
    "origem": "integer",
    "situacao": "integer",
    "created_at": "timestamp",
    "created_by": "json",
    "updated_at": "timestamp",
    "updated_by": "json",
    "lastupdate": "timestamp",
    "tenant": "integer",
    "trabalhador": {
        "trabalhador":  "guid",
        "agencia":  "guid",
        "numerocontasalario": "string",
        "numerocontasalariodv": "string",
        "salarioliquidoestimado": "string",
        "estabelecimento":  "guid"
    }
}

```

### Endpoint

> `{url_base}/solicitacoes/alteracoes-vts/`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca de todas as Solicitações de Alteração de Vt                     | object        |


### Response
#### Status: 200 - OK
```json
[
    {
        "solicitacao":  "guid",
        "codigo":"integer",
        "situacao":"integer",
        "created_at": "timestamp",
        "estabelecimento":  "guid",
        "trabalhador": {
            "trabalhador":  "guid",
            "numerocontasalario":"string",
            "numerocontasalariodv": "string",
            "salarioliquidoestimado": "string",
            "estabelecimento":  "guid"
        }
    },
    (...)
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

