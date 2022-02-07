## Solicitações de Férias

### Objetivo

Lista uma solicitação de férias

### Endpoint

> `{url_base}/solicitacoes/ferias/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Lista uma solicitação de férias                                         | object       |


### Response
#### Status: 200 - OK
```json
{
    "dataaviso":  "string",
    "solicitacao":  "string",
    "datainiciogozo":  "string",
    "datafimgozo":  "string",
    "diasferiascoletivas": "integer",
    "datainicioperiodoaquisitivo": "string",
    "datafimperiodoaquisitivo": "string",
    "observacao": "integer",
    "adto13nasferias": "boolean",
    "diasvendidos": "integer",
    "situacao": "integer",
    "trabalhador": "string",
    "estabelecimento": "string",
    "tiposolicitacao": "integer",
    "codigo": "string",
    "justificativa": "string",
    "origem": "integer",
    "created_at": "string",
    "created_by": "json",
    "updated_at": "string",
    "updated_by": "json",
    "tenant": "integer",
    "temabonopecuniario": "boolean",
}   

```

#### Atributos
| Atributo                              | Descrição                                                              |
|---------------------------------------|------------------------------------------------------------------------|
| trabalhador                           | Identificador do colaborador                                           |
| dataaviso                             | A data de aviso da solicitação de férias                               |
| solicitacao                           | O guid da solicitação de férias                                        |
| datainiciogozo                        | A data de início de gozo da solicitação de férias                      |
| datafimgozo                           | A data de início de fim da solicitação de férias                       |
| diasferiascoletivas                   | A quantidade de dias de férias gozados na solicitação de férias        |
| datainicioperiodoaquisitivo           | Data do inicio do período aquisitivo na solicitação de férias          |
| datafimperiodoaquisitivo              | Data de fim do período aquisitivo na solicitação de férias             |
| observacao                            | A observação da solicitação                                            |
| adto13nasferias                       | Se o colaborador adiantou o décimo terceiro na solicitação             |
| diasvendidos                          | A quantidade de dias de férias vendidos na solicitação                 |
| situacao                              | A situação da solicitação                                              |
| estabelecimento                       | O estabelecimento onde é feita a solicitação                           |
| tiposolicitacao                       | O tipo da solicitaçãop                                                 |
| codigo                                | O código da solicitação                                                |
| justificativa                         | A justificativa da solicitação                                         |
| origem                                | A origem da solicitação (Meurh/MeuTrabalho)                            |
| created_at                            | Timestamp da criação                                                   |
| created_by                            | Json do usuário que fez a criação                                      |
| updated_at                            | Timestamp do último update                                             |
| updated_by                            | Json do usuário que fez o update                                       |
| tenant                                | O tenant da solicitação                                                |
| temabonopecuniario                    | Se a solicitação possui abono pecuniário                               |


