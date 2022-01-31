import angular = require('angular');
import { ProjetosTemplatesService } from '../projetos-templates.service';

export class ModalTemplateController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors', 'projetosTemplatesService', '$rootScope'];

    public action: string;
    public form: any;
    
    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        public projetosTemplatesService: ProjetosTemplatesService,
        public $rootScope: angular.IRootScopeService,
    ) {
    }

    submit() {
        
        this.form.$submitted = true;
        if (this.form.$valid) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
        this.$rootScope.$broadcast('ativar_botao_template');

    }

    close() {
        this.$uibModalInstance.close();
        this.$rootScope.$broadcast('ativar_botao_template');
    }

    async desfazerVinculo(entity: any){

        await this.projetosTemplatesService.deleteSobrescrito(entity.dadosprojeto.projeto);
        this.close();
        
    }

}

export class ModalTemplateService {
    static $inject = ['$uibModal', 'nsjRouting', '$http', 'projetosTemplatesService'];

    constructor(public $uibModal: any, public nsjRouting: any, public $http: any, public projetosTemplatesService: ProjetosTemplatesService) {
    }
    
    async open(dadosprojeto: any, desfazerVinculo: boolean) {

        let infoTemplate: any;

        //Se a modal for aberta no modo de edição, preciso fazer get para buscar informações do template projeto
        if(dadosprojeto.template){

            infoTemplate = await this.projetosTemplatesService.getSobrescrito(dadosprojeto.projeto);

        }

        return this.$uibModal.open({
            template: require('./modal-template.html'),
            controller: 'modalTemplateController',
            controllerAs: 'mdl_tmplt_cntrllr',
            windowClass: 'modal-md-wrapper',
            backdrop: 'static',
            resolve: {
                entity: async () => {

                    //Se for uma edição, carrego modal com informações buscadas no GET
                    let entity = dadosprojeto.template ? 
                    {
                        'dadosprojeto': dadosprojeto,
                        'desfazerVinculo': desfazerVinculo,
                        'templatenome': infoTemplate.templatenome,
                        'templatedescricao': infoTemplate.templatedescricao
                    }
                        :
                    {
                        'dadosprojeto': dadosprojeto,
                        'desfazerVinculo': desfazerVinculo
                    };

                    return entity;
                },
                constructors: () => {
                    return dadosprojeto;
                }
            }
        });

    }
}
