## Solicitações de Férias

### Objetivo

Lista os períodos aquisitivos abertos de um colaborador 

### Endpoint

> `{url_base}/solicitacoes/ferias/periodos/{id}`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /agrupado/{id}    | Lista os períodos aquisitivos abertos de um colaborador                 | object        |


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
    "inicioperiodoaquisitivoferiasatual": "string",
    "fimperiodoaquisitivoferiasatual": "string",
    "periodosaquisitivos": [ {
            "inicioperiodoaquisitivo": "string",
            "fimperiodoaquisitivo": "string",
            "direito": "integer",
            "saldo": "integer",
            "possuirascunho": "boolean",
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
| inicioperiodoaquisitivoferiasatual                 | Data do inicio do período aquisitivo atual do colaborador              |
| fimperiodoaquisitivoferiasatual                    | Data do fim do período aquisitivo do atual colaborador                 |
| periodosaquisitivos                                | A lista de períodos aquisitivos abertos para o colaborador             |
| periodosaquisitivos.inicioperiodoaquisitivo        | Data do inicio do período aquisitivo                                   |
| periodosaquisitivos.fimperiodoaquisitivo           | Data de fim do período aquisitivo                                      |
| periodosaquisitivos.direito                        | Dias de férias que o colaborador possui de direito no período          |     
| periodosaquisitivos.saldo                          | O saldo atual do colaborador no período                                |     
| periodosaquisitivos.possuirascunho                 | Se o período possui solicitações em rascunho                           |     

