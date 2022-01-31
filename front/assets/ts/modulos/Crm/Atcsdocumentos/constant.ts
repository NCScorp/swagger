import angular = require('angular');

export const FIELDS_CrmAtcsdocumentos = [
        {
                value: 'negocio.nome',
                label: 'Atendimento',
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
                options: { 'Pendente': 'entity.status == "0"', 'Recebido': 'entity.status == "1"', 'Pré-Aprovado': 'entity.status == "2"', 'Enviado para Requisitante': 'entity.status == "3"', 'Aprovado': 'entity.status == "4"', 'Recusado': 'entity.status == "5"', },
                label_class: '{"": entity.status === 0,                    "label-danger": entity.status === 5,                    "label-primary": entity.status === 3,                    "label-warning": entity.status === 1,                    "label-success": entity.status === 2 || entity.status === 4,}',
        },

        {
                value: 'tipodocumento.nome',
                label: 'Documento',
                type: 'string',
                style: 'title',
                copy: '',
        },

        {
                value: 'tipodocumento.tipodocumento',
                label: 'Tipo Documento',
                type: 'guid',
                style: 'default',
                copy: '',
        },

        {
                value: 'created_at',
                label: 'Data do Recebimento',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

        {
                value: 'updated_at',
                label: 'Data do Atendimento',
                type: 'datetime',
                style: 'default',
                copy: '',
        },

]

export const OPTIONS_CrmAtcsdocumentos = { 'tipodocumento.tipodocumento': 'tipodocumento.tipodocumento', }
export const MAXOCCURS_CrmAtcsdocumentos = { 'status': 1, }
export const SELECTS_CrmAtcsdocumentos = {}
export const SELECT_Status_CrmAtcsdocumentos = {
        '0': 'Pendente',
        '1': 'Recebido',
        '2': 'Pré-Aprovado',
        '3': 'Enviado para Requisitante',
        '4': 'Aprovado',
        '5': 'Recusado',
}