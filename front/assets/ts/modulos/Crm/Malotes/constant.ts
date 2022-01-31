import angular = require('angular');

export const FIELDS_CrmMalotes = [
        {
                value: 'codigo',
                label: 'Código',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'dtenvio',
                label: 'Envio',
                type: 'date',
                style: 'default',
                copy: '',
        },

        {
                value: 'requisitantecliente.razaosocial',
                label: 'Requisitante',
                type: 'string',
                style: 'default',
                copy: '',
        },

        {
                value: 'status',
                label: 'Situação',
                type: 'options',
                style: 'label',
                copy: '',
                options: { 'Novo': 'entity.status == "0"', 'Enviado': 'entity.status == "1"', 'Aceito': 'entity.status == "2"', 'Aceito parcialmente': 'entity.status == "3"', 'Recusado': 'entity.status == "4"', 'Fechado': 'entity.status == "5"', },
        },

        {
                value: 'dtresposta',
                label: 'Data de resposta do requisitante',
                type: 'datetime',
                style: 'sup',
                copy: '',
        },

];


