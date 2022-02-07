### Dados Atuais de Vt


### Endpoint

> `{url_base}/meusdados/vt`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca os dados atuais de vt do colaborador                              | object        |



### Response
#### Status: 200 - OK
```json
[
    {
        "tarifaconcessionariavttrabalhador": "guid",
        "trabalhador":  "guid",
        "tenant": "integer",
        "quantidade": "integer",
        "tarifaconcessionariavt": {
            "tarifaconcessionariavt":  "guid",
            "codigo": "string",
            "descricao": "string",
            "tipo": "integer",
            "valor": "string",
            "concessionariavt": {
                "concessionariavt":  "guid",
                "nome": "string",
                "codigo": "string",
                "tenant": "integer"
            },
            "tenant": "integer"
        }
    },
    (...)
]
```