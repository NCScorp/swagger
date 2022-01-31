import angular = require('angular');
export const FIELDS_CrmHistoricospadrao = [
        {
                value: 'codigo',
                label: '',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'tipo',
                label: 'Tipo',
                type: 'options',
                style: 'default',
                copy: '',
                options: { 'Geral': 'entity.tipo == "100"', 'Acompanhamento': 'entity.tipo == "101"', 'Pendências': 'entity.tipo == "102"', },
        },

];


