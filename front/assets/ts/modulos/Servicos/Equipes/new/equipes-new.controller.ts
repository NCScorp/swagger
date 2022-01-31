import angular = require('angular');
import { EquipesService } from '../equipes.service';
export class EquipesNewController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'equipesService',
        'toaster',
        'entity'
    ];

    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public equipesService: EquipesService,
        public toaster: any,
        public entity: any
    ) {

        this.constructors = equipesService.constructors;
        $scope.$on('equipes_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao inserir equipe!'
            });
            this.$state.go('equipes_show', angular.extend({}, this.equipesService.constructors, { 'equipetecnico': args.entity.equipetecnico }));
        });
        $scope.$on('equipes_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.equipesService.save(this.entity);
                }
            } else {
                if (typeof (args.response.data) !== 'undefined' && args.response.data.message) {
                    if (args.response.data.message === 'Validation Failed') {
                        let distinct = (value: any, index: any, self: any) => {
                            return self.indexOf(value) === index;
                        };
                        args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                        let message = this.utilService.parseValidationMessage(args.response.data);
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: 'Erro de Validação',
                                body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                bodyOutputType: 'trustedHtml'
                            });
                    } else {
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: args.response.data.message
                            });
                    }
                } else {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: 'Ocorreu um erro ao inserir Equipes!'
                        });
                }
            }

        });
    }

    submit() {
        this.form.$submitted = true;

        let msg = this.validaDados();

        if (this.form.$valid && !this.entity.$$__submitting) {
            this.equipesService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: msg
            });
        }
    }

    validaDados() {
        const temMenosDeDoisTecnicos = (this.entity.equipetecnicotecnico && this.entity.equipetecnicotecnico.length < 2);
        let msg = 'Alguns campos do formulário apresentam erros.';

        if (!this.entity.equipetecnicotecnico) {
            this.form.$valid = false;
            return msg;
        }

        let responsavel = this.entity.equipetecnicotecnico.filter(item => item.responsavel == true);

        if (!responsavel.length) {
            this.form.$valid = false;
            return 'Deve haver um responsável na lista de técnicos. Por favor, selecione.';
        }

        if (temMenosDeDoisTecnicos) {
            this.form.$valid = false;
        }

        return msg;
    }
}
