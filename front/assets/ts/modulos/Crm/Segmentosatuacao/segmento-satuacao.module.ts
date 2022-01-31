import angular from "angular";
import { CrmSegmentosatuacaoListController } from ".";
import { crmSegmentosatuacaoDefault, crmSegmentosatuacaoDefaultShow } from "./components";
import { SegmentosatuacaoRoutes } from "./config";
import { FIELDS_CrmSegmentosatuacao } from "./constant";
import { CrmSegmentosatuacaoDefaultController } from "./default.form";
import { CrmSegmentosatuacaoDefaultShowController } from "./default.form.show";
import { CrmSegmentosatuacaoFormController } from "./edit";
import { CrmSegmentosatuacao } from "./factory";
import { CrmSegmentosatuacaoFormNewController } from "./new";
import { CrmSegmentosatuacaoFormShowController } from "./show";
import { ModalExclusaoSegmentoAtuacaoController, ModalExclusaoSegmentoAtuacaoService } from "./modal-exclusao";

export const SegmentosatuacaoModule = angular
    .module('SegmentosatuacaoModule', [])
    .controller('CrmSegmentosatuacaoDefaultShowController', CrmSegmentosatuacaoDefaultShowController)
    .controller('CrmSegmentosatuacaoDefaultController', CrmSegmentosatuacaoDefaultController)
    .controller('CrmSegmentosatuacaoFormController', CrmSegmentosatuacaoFormController)
    .service('CrmSegmentosatuacao', CrmSegmentosatuacao)
    .controller('CrmSegmentosatuacaoListController', CrmSegmentosatuacaoListController)
    .controller('CrmSegmentosatuacaoFormNewController', CrmSegmentosatuacaoFormNewController)
    .controller('CrmSegmentosatuacaoFormShowController', CrmSegmentosatuacaoFormShowController)
    .service('modalExclusaoSegmentoAtuacaoService', ModalExclusaoSegmentoAtuacaoService)
    .controller('ModalExclusaoSegmentoAtuacaoController', ModalExclusaoSegmentoAtuacaoController)
    .component('crmSegmentosatuacaoDefault', crmSegmentosatuacaoDefault)
    .component('crmSegmentosatuacaoDefaultShow', crmSegmentosatuacaoDefaultShow)
    .constant('FIELDS_CrmSegmentosatuacao', FIELDS_CrmSegmentosatuacao)
    .run(['$rootScope', 'FIELDS_CrmSegmentosatuacao',
        ($rootScope: any, FIELDS_CrmSegmentosatuacao: object) => {
            $rootScope.FIELDS_CrmSegmentosatuacao = FIELDS_CrmSegmentosatuacao;
        }])
    .config(SegmentosatuacaoRoutes)
    .name

