import angular = require('angular');

export const FIELDS_CrmAtcsfollowups = [
        {
                value: 'negociofollowup',
                label: '',
                type: 'guid',
                style: 'title',
                copy: '',
        },

        {
                value: 'realizadoem',
                label: '',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

        {
                value: 'created_at',
                label: '',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

        {
                value: 'created_by',
                label: '',
                type: 'json',
                style: 'default',
                copy: '',
        },

        {
                value: 'historico',
                label: '',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'receptor',
                label: '',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'meiocomunicacao',
                label: '',
                type: 'options',
                style: 'default',
                copy: '',
                options: { 'Telefone': 'entity.meiocomunicacao == "1"', 'Email': 'entity.meiocomunicacao == "2"', 'Pessoalmente': 'entity.meiocomunicacao == "3"', 'Outro': 'entity.meiocomunicacao == "4"', },
        },

        {
                value: 'tenant',
                label: '',
                type: 'integer',
                style: 'default',
                copy: '',
        },

        {
                value: 'ativo',
                label: '',
                type: 'boolean',
                style: 'default',
                copy: '',
        },

        {
                value: 'figuracontato',
                label: '',
                type: 'options',
                style: 'default',
                copy: '',
                options: {
                        'Cliente': 'entity.figuracontato == "1"',
                        'Prestador': 'entity.figuracontato == "2"',
                        'Seguradora': 'entity.figuracontato == "3"',
                        'Colaborador': 'entity.figuracontato == "4"',
                        'Responsável': 'entity.figuracontato == "5"',
                        'Outro': 'entity.figuracontato == "6"',
                },
        },

        {
                value: 'totalanexos',
                label: '',
                type: 'integer',
                style: 'default',
                copy: '',
        },

]


