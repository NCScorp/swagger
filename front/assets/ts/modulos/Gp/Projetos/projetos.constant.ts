
        export const FIELDS_Projetos = [
            {
                value: 'codigo',
                label: 'Código',
                type: 'string',
                style: 'title',
                copy: '',
            },
            {
                value: 'nome',
                label: 'Nome',
                type: 'string',
                style: 'title',
                copy: '',
            },{
                value: 'datafim',
                label: 'Prazo Final',
                type: 'date',
                style: 'title',
                copy: '',
            },{
                value: 'responsavel_conta_nasajon.nome',
                label: 'Responsável',
                type: 'string',
                style: 'title',
                copy: '',
            },{
                value: 'estabelecimento.nomefantasia',
                label: 'Estabelecimento',
                type: 'string',
                style: 'title',
                copy: '',
            },{
                value: 'cliente.nomefantasiacompleto',
                label: 'Cliente',
                type: 'string',
                style: 'title',
                copy: '',
            },
            {
                value: 'situacao',
                label: 'Situacao',
                type: 'options',
                style: 'label',
                copy: '',
                options: {
                    'Aberto': 'entity.situacao == "0"',  
                    'Cancelado': 'entity.situacao == "1"',
                    'Em andamento': 'entity.situacao == "2"',  
                    'Finalizado': 'entity.situacao == "3"',  
                    'Parado': 'entity.situacao == "4"',
                    'Aguardando a inicialização': 'entity.situacao == "6"',
                },
                label_class: `{
                    "label-primary": entity.situacao == "0" || entity.situacao == "6",
                    "label-default": entity.situacao == "1",
                    "label-success": entity.situacao == "2",
                    "label-danger": entity.situacao == "3",
                    "label-warning": entity.situacao == "4",
                }`,
            }
        ];

