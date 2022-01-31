import angular = require('angular');

export const FIELDS_NsAdvertencias = [
        {
                value: 'nome',
                label: 'Nome da Advertência',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'created_at',
                label: 'Data',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

        {
                value: 'motivo',
                label: 'Motivo',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'status',
                label: 'Status',
                type: 'options',
                style: 'default',
                copy: '',
                options: { 'Em vigor': 'entity.status == "0"', 'Arquivada': 'entity.status == "1"', 'Excluída': 'entity.status == "2"', },
        },

];


