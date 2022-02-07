
### Solicitação de Falta

### Endpoint

> `{url_base}/solicitacoes/faltas`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| POST    | /                 |  Criação da Solicitação de Falta                                        | object        |


### Request
```json

{
    "id": "guid",
    "mesdescontocalculo": "integer",
    "anodescontocalculo": "integer",
    "situacao": "integer",
    "justificada": "boolean",
    "trabalhador": "guid",
    "tipojustificativa": "integer",
    "datas": [
        "timestamp"
    ]
}


```


### Response
#### Status: 200 - OK
```json
{
    "solicitacao": "guid",
    "estabelecimento": "guid",
    "tiposolicitacao":  "integer",
    "codigo":  "integer",
    "justificativa":"string",
    "observacao":"string",
    "origem":  "integer",
    "situacao":  "integer",
    "created_at": "string",
    "created_by": "json",
    "updated_at": "string",
    "updated_by": "json",
    "lastupdate": "string",
    "tenant":  "integer",
    "data": "timestamp",
    "datas": [
        "timestamp"
    ],
    "justificada": "boolean",
    "descontaponto": "boolean",
    "compensacao": "boolean",
    "mesdescontocalculo":  "integer",
    "anodescontocalculo":  "integer",
    "trabalhador": "guid"
}


```

### Endpoint

> `{url_base}/solicitacoes/faltas/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| PUT     | /{id}             | Alteração da Solicitação de Falta                                       | object        |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |


### Request
```json

{
    "solicitacao": "guid",
    "estabelecimento": "guid",
    "tiposolicitacao":  "integer",
    "codigo":  "integer",
    "justificativa":"string",
    "observacao":"string",
    "origem":  "integer",
    "situacao":  "integer",
    "created_at": "string",
    "created_by": "json",
    "updated_at": "string",
    "updated_by": "json",
    "lastupdate": "string",
    "tenant":  "integer",
    "data": "timestamp",
    "datas": [
        "timestamp"
    ],
    "justificada": "boolean",
    "descontaponto": "boolean",
    "compensacao": "boolean",
    "mesdescontocalculo":  "integer",
    "anodescontocalculo":  "integer",
    "trabalhador": "guid",
}


```  


### Response
#### Status: 200 - OK
```json
{
}   

```

### Endpoint

> `{url_base}/solicitacoes/faltas/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| DELETE  | /{id}             |  Excluir uma Solicitação de Falta                                       | object        |

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

> `{url_base}/solicitacoes/faltas/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca de uma Solicitação de Falta                                       | object        |

#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador da solicitação                                           |



### Response
#### Status: 200 - OK
```json

{
    "solicitacao": "guid",
    "estabelecimento": "guid",
    "tiposolicitacao":  "integer",
    "codigo":  "integer",
    "justificativa":"string",
    "observacao":"string",
    "origem":  "integer",
    "situacao":  "integer",
    "created_at": "string",
    "created_by": "json",
    "updated_at": "string",
    "updated_by": "json",
    "lastupdate": "string",
    "tenant":  "integer",
    "data": "timestamp",
    "datas": [
        "timestamp"
    ],
    "justificada": "boolean",
    "descontaponto": "boolean",
    "compensacao": "boolean",
    "mesdescontocalculo":  "integer",
    "anodescontocalculo":  "integer",
    "trabalhador": "guid"
}



```

### Endpoint

> `{url_base}/solicitacoes/faltas/`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 |  Busca de todas as Solicitações de Faltas                               | object        |


### Response
#### Status: 200 - OK
```json

[
    {
        "solicitacao": "guid",
        "estabelecimento": "guid",
        "codigo": "integer",
        "situacao": "integer",
        "data": "timestamp",
        "datas": "json",
        "mesdescontocalculo": "integer",
        "anodescontocalculo": "integer",
        "created_at": "string",
        "created_by": "json",
        "trabalhador": "guid"
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

