import * as angular from 'angular';
import { NsSeriesService } from './series.service';

export const NsSeriesRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['nsSeriesService', '$stateParams', '$state', (nsSeriesService: NsSeriesService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.serie) {
                    return new Promise((resolve: any, reject: any) => {
                        nsSeriesService.get($stateParams.serie)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_series_not_found', $stateParams)) {
                                        $state.go('ns_series_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    return {};
                }
            }
        }]
    };
    let resolveFilter = {
    };
    $stateProvider
        .state('ns_series', {
            url: '/ns/series?q?situacao?estabelecimento',
            resolve: resolveFilter,
            template: require('./index/series-index.html'),
            controller: 'nsSeriesIndexController',
            controllerAs: 'ns_srs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_series_new', {
            parent: 'ns_series',
            url: '/new',
            resolve: resolve,
            template: require('./new/series-new.html'),
            controller: 'nsSeriesNewController',
            controllerAs: 'ns_srs_frm_nw_cntrllr'
        }).state('ns_series_show', {
            parent: 'ns_series',
            url: '/:serie',
            resolve: resolve,
            template: require('./show/series-show.html'),
            controller: 'nsSeriesShowController',
            controllerAs: 'ns_srs_frm_shw_cntrllr'
        }).state('ns_series_edit', {
            parent: 'ns_series',
            url: '/:serie/edit',
            resolve: resolve,
            template: require('./edit/series-edit.html'),
            controller: 'nsSeriesEditController',
            controllerAs: 'ns_srs_frm_cntrllr'
        });
}];

