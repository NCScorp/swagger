import angular = require('angular');

export const FIELDS_GpTarefas = [
        {
                value: 'previsaoinicio',
                label: 'Previsão Início',
                type: 'datetime',
                style: 'title',
                copy: '',
        },

        {
                value: 'previsaotermino',
                label: 'Previsão Término',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

        {
                value: 'situacaostr',
                label: 'Situação',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'numerotarefa',
                label: 'Numero Tarefa',
                type: 'integer',
                style: 'default',
                copy: '',
        },

        {
                value: 'possui_ordemservico',
                label: 'possui_ordemservico',
                type: 'boolean',
                style: 'default',
                copy: '',
        },

];


