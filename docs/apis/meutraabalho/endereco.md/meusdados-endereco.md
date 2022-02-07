### Dados Atuais de Cadastro


### Endpoint

> `{url_base}/meusdados/endereco`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Busca os dados atuais cadastrais do colaborador                         | object        |



### Response
#### Status: 200 - OK
```json
{
    "municipioresidencia": {
        "ibge": "string",
        "nome":"string",
        "uf": "string"
    },
    "paisresidencia": {
        "pais": "string",
        "nome":"string",
        "lastupdate": "timestamp"
    },
    "logradouro": "string",
    "numero": "string",
    "complemento": "string",
    "bairro": "string",
    "cidade": "string",
    "cep": "string",
    "dddtel": "string",
    "telefone": "string",
    "dddcel": "string",
    "celular": "string",
    "email": "string",
    "tipologradouro": {
        "tipologradouro": "string",
        "descricao": "string",
        "lastupdate": "timestamp"
    },
    "solicitacoesalteracoesenderecosaberta": [
        {
            "solicitacao": "guid",
            "trabalhador": "guid",
            "codigo": "integer",
            "estabelecimento": "guid",
            "situacao": "integer",
            "created_at": "2021-06-29 20:04:54.52943+00"
        },
        (..)
    ]
}
```