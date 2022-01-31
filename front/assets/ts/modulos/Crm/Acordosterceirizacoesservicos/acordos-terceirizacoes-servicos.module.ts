import angular from "angular";
import { crmAcordosterceirizacoesservicosDefaultShow, crmAcordosterceirizacoesservicosDefault } from "./components";
import { acordosterceirizacoesservicosRoutes } from "./config";
import { CrmAcordosterceirizacoesservicosDefaultShowController } from "./default.form.show";
import { CrmAcordosterceirizacoesservicosDefaultController } from "./default.form";
import { CrmAcordosterceirizacoesservicosFormController } from "./edit";
import { CrmAcordosterceirizacoesservicos } from "./factory";
import { CrmAcordosterceirizacoesservicosListController } from ".";
import { ModalExclusaoAcordoTerceirizacaoServService, ModalExclusaoAcordoTerceirizacaoServController } from "./modal-exclusao";
import { CrmAcordosterceirizacoesservicosFormNewController } from "./new";
import { CrmAcordosterceirizacoesservicosFormShowController } from "./show";

export const acordosterceirizacoesservicosModule = angular.module('acordosterceirizacoesservicosModule', [])
    .component('crmAcordosterceirizacoesservicosDefaultShow', crmAcordosterceirizacoesservicosDefaultShow)
    .component('crmAcordosterceirizacoesservicosDefault', crmAcordosterceirizacoesservicosDefault)
    .controller('CrmAcordosterceirizacoesservicosDefaultShowController', CrmAcordosterceirizacoesservicosDefaultShowController)
    .controller('CrmAcordosterceirizacoesservicosDefaultController', CrmAcordosterceirizacoesservicosDefaultController)
    .controller('CrmAcordosterceirizacoesservicosFormController', CrmAcordosterceirizacoesservicosFormController)
    .service('CrmAcordosterceirizacoesservicos', CrmAcordosterceirizacoesservicos)
    .controller('CrmAcordosterceirizacoesservicosListController', CrmAcordosterceirizacoesservicosListController)
    .service('ModalExclusaoAcordoTerceirizacaoServService', ModalExclusaoAcordoTerceirizacaoServService)
    .controller('ModalExclusaoAcordoTerceirizacaoServController', ModalExclusaoAcordoTerceirizacaoServController)
    .controller('CrmAcordosterceirizacoesservicosFormNewController', CrmAcordosterceirizacoesservicosFormNewController)
    .controller('CrmAcordosterceirizacoesservicosFormShowController', CrmAcordosterceirizacoesservicosFormShowController)
    .config(acordosterceirizacoesservicosRoutes)
    .constant('FIELDS_CrmAcordosterceirizacoesservicos', [
        {
                value: 'prestador',
                label: 'Prestador',
                type: 'string',
                style: 'title',
                copy: '',
        },
        {
                value: 'servico',
                label: 'Serviço',
                type: 'string',
                style: 'title',
                copy: '',
        },
        {
                value: 'valor',
                label: 'Valor',
                type: 'float',
                style: 'title',
                copy: '',
        },

    ])
    .run(
        ['$rootScope', 'FIELDS_CrmAcordosterceirizacoesservicos',
            ($rootScope: any, FIELDS_CrmAcordosterceirizacoesservicos: object) => {
                $rootScope.FIELDS_CrmAcordosterceirizacoesservicos = FIELDS_CrmAcordosterceirizacoesservicos;
            }
        ]
    ).name