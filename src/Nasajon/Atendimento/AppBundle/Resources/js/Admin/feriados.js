angular.module('mda')
    .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
        var resolve = {
            'entity': ['AtendimentoFeriados', '$stateParams', function (entityService, $stateParams) {
                if ($stateParams['entity']) {
                    return $stateParams['entity'];
                } else {
                    if ($stateParams['feriado']) {                        
                        return entityService.get($stateParams['feriado']);
                    } else {
                        return {};
                    }
                }
            }]
        };

        $stateProvider.state('atendimento_feriados', {
            url: "/feriados",
            parent: 'atendimento_admin_configuracoes',
            templateUrl: nsjRoutingProvider.$get().generate('atendimento_feriados_template'),
            controller: 'AtendimentoFeriadosListController',
            controllerAs: 'tndmnt_frds_lst_cntrllr'
        }).state('atendimento_feriados_new', {
            parent: 'atendimento_feriados',
            url: "/new",
            resolve: resolve,
            templateUrl: nsjRoutingProvider.$get().generate('atendimento_feriados_template_form'),
            controllerAs: 'tndmnt_frds_frm_cntrllr',
            controller: 'AtendimentoFeriadosFormController'
        }).state('atendimento_feriados_edit', {
            parent: 'atendimento_feriados',
            url: "/:feriado/edit",
            resolve: resolve,
            templateUrl: nsjRoutingProvider.$get().generate('atendimento_feriados_template_form'),
            controller: 'AtendimentoFeriadosFormController',
            controllerAs: 'tndmnt_frds_frm_cntrllr'
        });
    }])
    .controller('AtendimentoFeriadosFormController', [
        '$scope', '$stateParams', '$state', 'AtendimentoFeriados', 'entity', 'toaster',
        function ($scope, $stateParams, $state, entityService, entity, toaster) {

            var self = this;
            self.submitted = false;
            self.entity = entity;
            self.constructors = {};


            self.submit = function () {
                self.submitted = true;
                if (self.form.$valid && !self.entity.$$__submitting) {
                    entityService.save(self.entity);
                } else {
                    toaster.pop({
                        type: 'error',
                        title: 'Alguns campos do formulário apresentam erros'
                    });
                }
            };

            self.onSubmitSuccess = $scope.$on("atendimento_feriados_submitted", function (event, args) {
                var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao ' + acao + ' Feriado!'
                });
                $state.go('atendimento_feriados', self.constructors);
            });

            self.onSubmitError = $scope.$on("atendimento_feriados_submit_error", function (event, args) {
                if (args.response.status == 409) {
                    if (confirm(args.response.data.message)) {
                        self.entity[''] = args.response.data.entity[''];
                        entityService.save(self.entity);
                    }
                } else {
                    if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                        toaster.pop(
                            {
                                type: 'error',
                                title: args.response.data.message
                            });
                    } else {
                        var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                        toaster.pop(
                            {
                                type: 'error',
                                title: "Ocorreu um erro ao tentar " + acao + "."
                            });
                    }
                }
            });
            self.delete = function (force) {
                entityService.delete($stateParams['feriado'], force);
            };

            self.onDeleteSuccess = $scope.$on("atendimento_feriados_deleted", function (event, args) {
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao excluir Feriado!'
                });

                $state.go('atendimento_feriados', angular.extend(self.constructors));
            });

            self.onDeleteError = $scope.$on("atendimento_feriados_delete_error", function (event, args) {
                if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    toaster.pop(
                        {
                            type: 'error',
                            title: args.response.data.message
                        });
                } else {
                    toaster.pop(
                        {
                            type: 'error',
                            title: "Ocorreu um erro ao tentar excluir."
                        });
                }
            });

            for (var i in $stateParams) {
                if (i != 'entity') {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }
            }
        }]);
