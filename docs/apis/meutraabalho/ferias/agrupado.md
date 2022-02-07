## Solicitações de Férias

### Objetivo

Lista as solicitações agrupadas de uma colaborador

### Endpoint

> `{url_base}/solicitacoes/ferias/agrupado/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /agrupado/{id}    | Lista as solicitações agrupadas de uma colaborador                      | object       |


#### Atributos no endpoint

| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| id                                                 | Identificador do colaborador                                           |

### Response
#### Status: 200 - OK
```json
{
    "trabalhador": "guid",
    "nome": "string",
    "codigo": "string",
    "tipo": "integer",
    "cbo": "string",
    "dataadmissao": "string",
    "datarescisao": "string",
    "inicioperiodoaquisitivoferias": "string",
    "faltaparadobra": "integer",
    "anoperiodo": "integer",
    "datalimitegozo": "string",
    "inicioproximoperiodoaquisitivoferias": "string",
    "fimperiodoaquisitivoferias": "string",
    "created_at": "string",
    "periodosaquisitivos": [
        "2017-01-01|2018-01-01": {
            "datainicioperiodoaquisitivo": "string",
            "datafimperiodoaquisitivo": "string",
            "direito": "integer",
            "saldo": "integer",
            "possuirascunho": "boolean",
            "solicitacoes": [
                {
                "trabalhador":  "string",
                "tenant":  "integer",
                "estabelecimento":  "string",
                "dataaviso":  "string",
                "solicitacao":  "string",
                "datainiciogozo":  "string",
                "datafimgozo":  "string",
                "diasferiascoletivas": "integer",
                "datainicioperiodoaquisitivo": "string",
                "datafimperiodoaquisitivo": "string",
                "observacao": "integer",
                "tipo": "integer",
                "adto13nasferias": "boolean",
                "diasvendidos": "integer",
                "situacao": "integer",
                "avisoferiastrabalhador": "string",
                },
                (...)
            ]
        },
        (...)
    ]
}

```



#### Atributos do Retorno
| Atributo                                           | Descrição                                                              |
|----------------------------------------------------|------------------------------------------------------------------------|
| trabalhador                                        | Identificador do colaborador                                           |
| nome                                               | Nome do colaborador                                                    |
| codigo                                             | Código do colaborador                                                  |
| tipo                                               | Tipo do colaborador                                                    |
| cbo                                                | Cbo do colaborador                                                     |
| dataadmissao                                       | Data de admissão do colaborador                                        |
| datarescisao                                       | Data de rescisão do colaborador                                        |
| inicioperiodoaquisitivoferias                      | Data do inicio do período aquisitivo do colaborador                    |
| fimperiodoaquisitivoferias                         | Data do fim do período aquisitivo do colaborador                       |
| periodosaquisitivos                                | As solicitacões/avisos agrupados por períodos aquisitivos              |
| periodosaquisitivos.datainicioperiodoaquisitivo    | Data do inicio do período aquisitivo na solicitação de férias          |
| periodosaquisitivos.datafimperiodoaquisitivo       | Data de fim do período aquisitivo na solicitação de férias             |
| periodosaquisitivos.datafimperiodoaquisitivo       | Data de fim do período aquisitivo na solicitação de férias             |
| periodosaquisitivos.solicitacoes                   | As solicitacões/avisos do período aquisitivos                          |
| periodosaquisitivos.direito                        | Dias de férias que o colaborador possui de direito no período          | 
| periodosaquisitivos.saldo                          | O saldo atual do colaborador no período                                |    
| periodosaquisitivos.possuirascunho                 | Se o período possui solicitações em rascunho                           |     
| solicitacoes.dataaviso                             | A data de aviso da solicitação de férias                               |
| solicitacoes.solicitacao                           | O guid da solicitação de férias                                        |
| solicitacoes.datainiciogozo                        | A data de início de gozo da solicitação de férias                      |
| solicitacoes.datafimgozo                           | A data de início de fim da solicitação de férias                       |
| solicitacoes.diasferiascoletivas                   | A quantidade de dias de férias gozados na solicitação de férias        |
| solicitacoes.datainicioperiodoaquisitivo           | Data do inicio do período aquisitivo na solicitação de férias          |
| solicitacoes.datafimperiodoaquisitivo              | Data de fim do período aquisitivo na solicitação de férias             |
| solicitacoes.observacao                            | A observação da solicitação                                            |
| solicitacoes.tipo                                  | O tipo da solicitação                                                  |
| solicitacoes.adto13nasferias                       | Se o colaborador adiantou o décimo terceiro na solicitação             |
| solicitacoes.diasvendidos                          | A quantidade de dias de férias vendidos na solicitação                 |
| solicitacoes.situacao                              | A situação da solicitação                                              |
| solicitacoes.avisoferiastrabalhador                | O guid do aviso de férias                                              |
| solicitacoes.calculado                             | Se o aviso foi calculado                                               |
| solicitacoes.trabalhador                           | O guid do colaborador da solicitação                                   |
| solicitacoes.estabelecimento                       | O guid do estabelecimento da solicitação                               |
| solicitacoes.tenant                                | O tenant da solicitação                                                |
| created_at                                         | Timestamp da criação                                                   |
