## Solicitações de Férias

### Objetivo

Atualiza uma solicitação de férias

### Endpoint

> `{url_base}/solicitacoes/ferias/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| PUT     | /{id}             | Atualiza uma solicitação de férias                               | object        |


### Request
```json
{
    "solicitacao": "string",
    "trabalhador":"string",
    "estabelecimento":"string",
    "tiposolicitacao":"integer",
    "situacao":"integer",
    "codigo":"integer",
    "justificativa":"string",
    "observacao":"string",
    "origem":"integer",
    "tenant":"integer",
    "datainiciogozo":"string",
    "datafimgozo":"string",
    "datainicioperiodoaquisitivo":"string",
    "datafimperiodoaquisitivo":"string",
    "temabonopecuniario":"boolean",
    "diasvendidos":"integer",
    "diasferiascoletivas":"integer",
    "adto13nasferias":"boolean"
}   

```


### Response
#### Status: 200 - OK
```json
{
}   

```