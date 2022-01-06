
angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['AtendimentoAdminEnderecosemails', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['enderecoemail']) {
                                    return entityService.get($stateParams['enderecoemail']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };

                $stateProvider
                        .state('atendimento_admin_enderecosemails', {
                            url: "/configuracoes/enderecosemails",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_enderecosemails_template'),
                            controller: 'Atendimento\Admin\EnderecosemailsListController',
                            controllerAs: 'tndmnt_dmn_ndrcsmls_lst_cntrllr'
                        })

                        .state('atendimento_admin_enderecosemails_new', {
                            parent: 'atendimento_admin_enderecosemails',
                            url: "/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_enderecosemails_template_form'),
                            controllerAs: 'tndmnt_dmn_ndrcsmls_frm_cntrllr',
                            controller: 'AtendimentoAdminEnderecosemailsFormController'
                        })
                        .state('atendimento_admin_enderecosemails_edit', {
                            parent: 'atendimento_admin_enderecosemails',
                            url: "/:enderecoemail/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_enderecosemails_template_form'),
                            controller: 'Atendimento\Admin\EnderecosemailsFormController',
                            controllerAs: 'tndmnt_dmn_ndrcsmls_frm_cntrllr'
                        });
            }])
        .controller('AtendimentoAdminEnderecosemailsFormController', [
                '$scope', '$stateParams', '$state','toaster', 'AtendimentoAdminEnderecosemails', 'entity', function ($scope, $stateParams, $state, toaster ,entityService ,entity ) {

            var self = this;
            self.submitted = false;
            self.entity = entity;
            self.constructors = {};
                        
            
            self.submit = function () {

                self.submitted = true;
                if (self.form.$valid && !self.entity.$$__submitting) {

                    var email = self.form.email.$viewValue;

                    if (email && !email.match(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/)) {
                        toaster.pop({
                            type: 'error',
                            title: 'O endereço de e-mail informado não é válido!'
                        });

                        return;
                    }

                    entityService.save(self.entity);
                    
                } else {
                    toaster.pop({
                        type: 'error',
                        title: 'Alguns campos do formulário apresentam erros'
                    });
                }
            };

            self.onSubmitSuccess = $scope.$on("atendimento_admin_enderecosemails_submitted", function(event, args){
                                    var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao '+acao+' Endereço de Email!'
                    });
                    $state.go('atendimento_admin_enderecosemails', self.constructors);
                            });

            self.onSubmitError = $scope.$on("atendimento_admin_enderecosemails_submit_error", function(event, args){
                                if (args.response.status == 409) {
                    if(confirm(args.response.data.message)){
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
                        var acao = ((args.response.config.method == "PUT") ? "atualizar": "inserir");
                        toaster.pop(
                        {
                            type: 'error',
                            title: "Ocorreu um erro ao tentar " + acao + "."
                        });
                    } 
                }
            });
            self.delete = function(force){
                entityService.delete($stateParams['enderecoemail'], force);
            };

            self.onDeleteSuccess = $scope.$on("atendimento_admin_enderecosemails_deleted", function(event, args){

                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao excluir Endereço de Email!'
                });
                
                $state.go('atendimento_admin_enderecosemails', angular.extend(self.constructors));
            });

            self.onDeleteError = $scope.$on("atendimento_admin_enderecosemails_delete_error", function(event, args){
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

            for(var i in $stateParams){
                if(i != 'entity'){
                    self.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
                }
            }
        }]);