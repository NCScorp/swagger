import * as angular from 'angular';
import { SituacaoEnum } from './enums/situacaoEnum';
import { IProjeto } from './interfaces/projetos.interface';
import { ProjetosService } from './projetos.service';

export const ProjetosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {
  /**
   * Resolve de entity do Projeto selecionado para injetar no projetosShowEditController
   */
  let resolve = {
    'entity': ['projetosService', '$stateParams', 'toaster', '$state', (projetosService: ProjetosService, $stateParams: ng.ui.IStateParamsService, toaster: any, $state: ng.ui.IStateService) => {
      if ($stateParams.projeto) {
        return new Promise((resolve, reject) => {
          projetosService.get($stateParams.projeto).then((data: IProjeto) => {
            data.responsavel_conta_nasajon = JSON.parse(data.responsavel_conta_nasajon);
            resolve(data);
          }).catch((error) => {
            toaster.pop({
              type: 'error',
              title: 'Ocorreu um erro ao carregar o projeto.'
            });
            $state.go('projetos');
            reject(error);
          });
        });
      } else {
        return {};
      }
    }]
  };

  $stateProvider
    .state('projetos', {
      url: '/projetos?q?cliente',
      template: require('./index/projetos-index.html'),
      controller: 'projetosIndexController',
      controllerAs: 'pjt_lst_cntrllr',
      reloadOnSearch: false
    })
    .state('projetos_new', {
      url: '/projetos/new?&cliente',
      resolve: resolve,
      template: require('./wizard/projetos-new.html'),
      controller: 'projetosNewController',
      controllerAs: 'pjt_nw_cntrllr',
    })
    .state('projetos_edit', {
      url: '/projetos/:projeto/edit?&cliente',
      resolve: resolve,
      template: require('./wizard/projetos-new.html'),
      controller: 'projetosNewController',
      controllerAs: 'pjt_nw_cntrllr',
    })
    .state('projetos_show', {
      url: '/projetos/:projeto',
      template: require('./showedit/projetos-showedit.html'),
      resolve: resolve,
      controller: 'projetosShowEditController',
      controllerAs: 'pjts_shwdt_cntrllr',
    });
}];
