
import * as angular from 'angular';

import { OperacoesOrdensServicosService } from './operacoes-ordens-servicos.service';

import { OperacoesOrdensServicosRouting } from './operacoes-ordens-servicos.routes';

import { OperacoesOrdensServicosIndexController } from './index/operacoes-ordens-servicos-index.controller'
import { OperacoesOrdensServicosEditController } from './edit/operacoes-ordens-servicos-edit.controller'
import { OperacoesOrdensServicosNewController } from './new/operacoes-ordens-servicos-new.controller'
import { OperacoesOrdensServicosShowController } from './show/operacoes-ordens-servicos-show.controller'
import { OperacoesOrdensServicosShowFormController } from './showform/operacoes-ordens-servicos-show-form.controller'
import { OperacoesOrdensServicosFormController } from './form/operacoes-ordens-servicos-form.controller'

import { OperacoesOrdensServicosShowFormComponent } from './showform/operacoes-ordens-servicos-show-form.component'
import { OperacoesOrdensServicosFormComponent } from './form/operacoes-ordens-servicos-form.component'

export const OperacoesOrdensServicosModule = angular.module('operacoesOrdensServicosModule', ['ui.router.state'])
    .controller('operacoesOrdensServicosIndexController', OperacoesOrdensServicosIndexController)
    .controller('operacoesOrdensServicosEditController', OperacoesOrdensServicosEditController)
    .controller('operacoesOrdensServicosNewController', OperacoesOrdensServicosNewController)
    .controller('operacoesOrdensServicosShowController', OperacoesOrdensServicosShowController)
    .controller('operacoesOrdensServicosShowFormController', OperacoesOrdensServicosShowFormController)
    .controller('operacoesOrdensServicosFormController', OperacoesOrdensServicosFormController)
    .component(OperacoesOrdensServicosShowFormComponent.selector, OperacoesOrdensServicosShowFormComponent)
    .component(OperacoesOrdensServicosFormComponent.selector, OperacoesOrdensServicosFormComponent)
    .service('operacoesOrdensServicosService', OperacoesOrdensServicosService)
    .config(OperacoesOrdensServicosRouting)
    .constant('FIELDS_Operacoesordensservicos', [
        {
            value: 'descricao',
            label: 'descricao',
            type: 'string',
            style: 'title',
            copy: '',
        },
    ])
    .run(['$rootScope', 'FIELDS_Operacoesordensservicos', ($rootScope: any, FIELDS_Operacoesordensservicos: object) => {
        $rootScope.FIELDS_Operacoesordensservicos = FIELDS_Operacoesordensservicos;
    }]).name;
