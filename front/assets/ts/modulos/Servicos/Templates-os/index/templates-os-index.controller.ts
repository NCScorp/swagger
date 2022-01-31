import angular = require('angular');
import { TransitionService } from '@uirouter/core';
import { NsjTelaListaClasses } from '../../../../shared/components/nsjtelalista/components/nsjtelalista.classes';
import { ITemplateOrdemServico } from '../interfaces/templates-os.interface';
import { TemplatesOrdemServicoService } from '../templates-os.service';
import { ControllerListaBase } from '../../../../shared/controllers/controller-lista-base/controller-lista-base';

export class TemplatesOrdemServicoIndexController extends ControllerListaBase<ITemplateOrdemServico> {
    static $inject = [
        '$scope',
        '$stateParams',
        '$state',
        'templatesOrdemServicoService',
        '$rootScope',
        '$location',
        '$transitions',
        'moment'
    ];

    constructor(
        $scope: angular.IScope,
        $stateParams: angular.ui.IStateParamsService,
        $state: angular.ui.IStateService,
        templatesOrdemServicoService: TemplatesOrdemServicoService,
        $rootScope: any,
        $location: angular.ILocationService,
        $transitions: TransitionService,
        public moment: any
    ) {
        // Chamo constructor pai
        super(
            $scope,
            $rootScope,
            $state,
            $stateParams,
            $location,
            $transitions,
            templatesOrdemServicoService
        );
    }
    
    protected criarConfiguracaoTela(): void {
        this.configLista = new NsjTelaListaClasses.Config(
            NsjTelaListaClasses.ETipoTela.ttBuiltIn,
            {
                singular: 'Template de Ordem de Serviço',
                artigoIndefinidoSingular: 'um',
                plural: 'Templates de Ordem de Serviço'
            },
            'ordemservicotemplate',
            'ordensservicos_templates',
            'servicos'  
        );
    }

    protected configurarCamposListagem(): void {
        this.configLista.configCamposListagemBuiltIn.push(new NsjTelaListaClasses.CampoListagemBuiltIn(
            'nome',
            'Nome',
            NsjTelaListaClasses.ECampoListagemBuiltInTipo.clbitPrincipal
        ));
    }

    protected getService(): TemplatesOrdemServicoService {
        return (<TemplatesOrdemServicoService>this.service);
    }
}
