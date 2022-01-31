
import * as angular from 'angular';

import { NsSeriesService } from './series.service';

import { NsSeriesRouting } from './series.routes';

import { NsSeriesIndexController } from './index/series-index.controller';
import { NsSeriesEditController } from './edit/series-edit.controller';
import { NsSeriesNewController } from './new/series-new.controller';
import { NsSeriesShowController } from './show/series-show.controller';
import { NsSeriesShowFormController } from './showform/series-show-form.controller';
import { NsSeriesFormController } from './form/series-form.controller';

import { NsSeriesShowFormComponent } from './showform/series-show-form.component';
import { NsSeriesFormComponent } from './form/series-form.component';

export const NsSeriesModule = angular.module('NsSeriesModule', ['ui.router.state'])
    .controller('nsSeriesIndexController', NsSeriesIndexController)
    .controller('nsSeriesEditController', NsSeriesEditController)
    .controller('nsSeriesNewController', NsSeriesNewController)
    .controller('nsSeriesShowController', NsSeriesShowController)
    .controller('nsSeriesShowFormController', NsSeriesShowFormController)
    .controller('nsSeriesFormController', NsSeriesFormController)
    .component(NsSeriesShowFormComponent.selector, NsSeriesShowFormComponent)
    .component(NsSeriesFormComponent.selector, NsSeriesFormComponent)
    .service('nsSeriesService', NsSeriesService)
    .config(NsSeriesRouting)
    .constant('FIELDS_NsSeries', [
        {
            value: 'estabelecimento.nomefantasia',
            label: 'Estabelecimento',
            type: 'string',
            style: 'title',
            copy: '',
        },

        {
            value: 'numero',
            label: 'Número',
            type: 'string',
            style: 'default',
            copy: '',
        },

        {
            value: 'inicio',
            label: 'Início',
            type: 'float',
            style: 'default',
            copy: '',
        },

        {
            value: 'fim',
            label: 'Fim',
            type: 'float',
            style: 'default',
            copy: '',
        },

        {
            value: 'proximo',
            label: 'Próximo',
            type: 'float',
            style: 'default',
            copy: '',
        },

    ])
    .constant('OPTIONS_NsSeries', { 'situacao': 'situacao', 'estabelecimento': 'estabelecimento', })
    .constant('MAXOCCURS_NsSeries', {})
    .constant('SELECTS_NsSeries', { 'situacao': { '0': 'Aberto', '1': 'Fechado', }, })
    .run(['$rootScope', 'FIELDS_NsSeries', 'OPTIONS_NsSeries', 'MAXOCCURS_NsSeries', 'SELECTS_NsSeries', ($rootScope: any, FIELDS_NsSeries: object, OPTIONS_NsSeries: object, MAXOCCURS_NsSeries: object, SELECTS_NsSeries: object) => {
        $rootScope.OPTIONS_NsSeries = OPTIONS_NsSeries;
        $rootScope.MAXOCCURS_NsSeries = MAXOCCURS_NsSeries;
        $rootScope.SELECTS_NsSeries = SELECTS_NsSeries;
        $rootScope.FIELDS_NsSeries = FIELDS_NsSeries;
    }]).name;
