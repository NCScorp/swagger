### Tipos de Documentos


### Endpoint

> `{url_base}/tiposdocumentoscolaboradores/`

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