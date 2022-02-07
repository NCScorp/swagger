# Banco

O banco da aplicação está separado do bancosweb (banco utilizado por grande parte dos módulos do ERP), por isso o versionamento do banco (arquivos de migração utilizados pelo doctrine) são salvos dentro do próprio projeto e além disso não há sincronia das tabelas com os programas desktop do ERP Nasajon.

## Comparação com BancosWeb <a name="bancosweb"></a>

Algumas estruturas de tabelas e functions foram baseadas nas existentes do bancos web, para que se um dia os bancos se unissem, a junção fosse menos traumática possível. Vamos registrar aqui quais estruturas tem compatibilidade (e o quanto é) em relação ao bancosWeb.

### Tenants <a name="tenants"></a>

A estrutura é a mesma.

Funções:

* ns.api_tenantnovo

### Municípios <a name="municipios"></a>

A estrutura é a mesma.

> Dump inicial com os municipios já está no arquivo de verionamento de banco.

### Tiposlogradouros <a name="tiposlogradouros"></a>

A estrutura é a mesma.

> Dump inicial com os tipos já está no arquivo de verionamento de banco.

### Gruposempresariais <a name="gruposempresariais"></a>

A estrutura é a mesma com acréscimo da coluna "functionversion" que guardará a versão da function de banco que inseriu os dados.

### Empresas <a name="empresas"></a>

A estrutura é a mesma com acréscimo da coluna "functionversion" que guardará a versão da function de banco que inseriu os dados.

### Estabelecimentos <a name="estabelecimentos"></a>

A estrutura é a mesma com acréscimo da coluna "functionversion" que guardará a versão da function de banco que inseriu os dados.

### Funções aproveitadas <a name="funcoes"></a>

* ns.api_montamensagemerro
* ns.api_montamensagemok