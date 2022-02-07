## Solicitações de Férias

### Objetivo

Listar todos os colaboradores com as suas solicitações de férias

### Endpoint

> `{url_base}/solicitacoes/ferias`

| Metodo  | URI               | Descrição                                                               | Response      |
|---------|-------------------|-------------------------------------------------------------------------|---------------|
| GET     | /                 | Lista os colaboradores com suas solicitações de férias                  | array         |


### Response
#### Status: 200 - OK
```json
[
    {
        "solicitacao": "guid",
        "dataaviso": "string",
        "datainiciogozo": "string",
        "datafimgozo": "string",
        "datainicioperiodoaquisitivo": "string",
        "datafimperiodoaquisitivo": "string",
        "diasvendidos": "integer",
        "diasferiascoletivas": "integer",
        "estabelecimento": "guid",
        "created_at": "string",
        "situacao": "integer",
        "trabalhador": {
            "trabalhador": "uuid",
            "codigo":  "string",
            "nome":  "string"
        }
    }, 
    (...)
]
 
```

#### Atributos
| Atributo                              | Descrição                                                              |
|---------------------------------------|------------------------------------------------------------------------|
| dataaviso                             | A data de aviso da solicitação de férias                               |
| solicitacao                           | O guid da solicitação de férias                                        |
| datainiciogozo                        | A data de início de gozo da solicitação de férias                      |
| datafimgozo                           | A data de início de fim da solicitação de férias                       |
| diasferiascoletivas                   | A quantidade de dias de férias gozados na solicitação de férias        |
| datainicioperiodoaquisitivo           | Data do inicio do período aquisitivo na solicitação de férias          |
| datafimperiodoaquisitivo              | Data de fim do período aquisitivo na solicitação de férias             |
| diasvendidos                          | A quantidade de dias de férias vendidos na solicitação                 |
| situacao                              | A situação da solicitação                                              |
| estabelecimento                       | O estabelecimento onde é feita a solicitação                           |
| trabalhador                           | O objeto do colaborador                                                |
| trabalhador.trabalhador               | O guid do colaborador                                                  |
| trabalhador.codigo                    | A matrícula do colaborador                                             |
| trabalhador.nome                      | O nome do colaborador                                                  |
| created_at                            | Timestamp da criação                                                   |



