import angular = require('angular');
export const FIELDS_CrmNegocios = [
        {
                value: 'numero',
                label: 'Código',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'cliente_documento',
                label: 'CPF/CNPJ',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'cliente_companhia',
                label: 'Nome',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'clientesegmentodeatuacao.descricao',
                label: 'Segmento',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'uf.uf',
                label: 'UF',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'clientemunicipioibge.nome',
                label: 'Cidade',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'clientereceitaanual',
                label: 'Faturamento',
                type: 'options',
                style: 'default',
                copy: '',
                options: { 'Até R$ 5 milhões': 'entity.clientereceitaanual == "5000000"', 'De R$ 5 milhões até 30 milhões': 'entity.clientereceitaanual == "30000000"', 'De R$ 30 milhões até 100 milhões': 'entity.clientereceitaanual == "100000000"', 'De R$ 100 milhões até 300 milhões': 'entity.clientereceitaanual == "300000000"', 'De R$ 300 milhões até 500 milhões': 'entity.clientereceitaanual == "500000000"', 'De R$ 500 milhões até 1 bilhão': 'entity.clientereceitaanual == "1000000000"', 'Mais de R$ 1 bilhão': 'entity.clientereceitaanual == "2000000000"', },
        },

        {
                value: 'clientecaptador.vendedor_nome',
                label: 'Atribuição',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'situacaoprenegocio.nome',
                label: 'Situação',
                type: 'string',
                style: 'default',
                copy: '',
        },

]
        ;


