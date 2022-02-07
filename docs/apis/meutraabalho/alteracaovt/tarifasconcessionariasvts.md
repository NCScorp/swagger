### Tarifas de VT


### Endpoint

> `{url_base}/tarifasconcessionariasvts/`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca as tarifas de vt cadastradas                                      | object        |



###  Parâmetros na url



| Atributo                                | Tipo               |
|-----------------------------------------|--------------------|
| estabelecimento                         | "guid"             |




### Response
#### Status: 200 - OK
```json
[
    {
        "tarifaconcessionariavt": "guid",
        "codigo": "string",
        "descricao": "string",
        "tipo": "integer",
        "valor": "string"
    },
,(...)
]

```