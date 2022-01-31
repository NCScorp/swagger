import angular = require('angular');

export const FIELDS_NsFornecedores = [
        {
                value: 'nomefantasia',
                label: 'Nome Fantasia',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'cnpj',
                label: 'Documento',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'municipionome',
                label: 'Município',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'status',
                label: 'Status',
                type: 'options',
                style: 'label',
                copy: '',
                options: { 'Ativa': 'entity.status == "0"', 'Suspensa': 'entity.status == "1"', 'Banida': 'entity.status == "2"', },
                label_class: '{                    "label-success": entity.status === "0",                    "label-danger": entity.status !== "0",}',
        },

];


