import angular = require('angular');

export const FIELDS_CrmPropostasitens = [
        {
                value: 'previsaodatahorainicio',
                label: 'Previsão de início',
                type: 'datetime',
                style: 'title',
                copy: '',
        },

        {
                value: 'previsaodatahorafim',
                label: 'Previsão de fim',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

        {
                value: 'itemdefaturamentovalor',
                label: 'Valor da execução do serviço',
                type: 'moeda',
                style: 'default',
                copy: '',
        },

        {
                value: 'quantidade',
                label: 'Quantidade',
                type: 'float',
                style: 'default',
                copy: '',
        },

        {
                value: 'composicao.nome',
                label: 'Serviço',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'propostasitensfamilias.propostaitem',
                label: 'Proposta Item',
                type: 'guid',
                style: 'default',
                copy: '',
        },

        {
                value: 'propostasitensfuncoes.propostaitem',
                label: 'Proposta Item',
                type: 'guid',
                style: 'default',
                copy: '',
        },

        {
                value: 'tarefa.previsaoinicio',
                label: 'Previsao Inicio',
                type: 'datetime',
                style: 'title',
                copy: '',
        },

        {
                value: 'observacaocomposicao',
                label: '',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'valorapolice',
                label: '',
                type: 'float',
                style: 'default',
                copy: '',
        },

];


