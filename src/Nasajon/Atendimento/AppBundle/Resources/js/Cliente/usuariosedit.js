angular.module('mda').controller('AtendimentoClienteUsuariosFormController', [
    '$scope', '$stateParams', '$state', 'AtendimentoClienteUsuarios', 'entity', 'toaster', function ($scope, $stateParams, $state, entityService, entity, toaster) {

        var self = this;
        self.submitted = false;
        self.entity = entity;
        self.constructors = {};


        self.submit = function () {
            self.submitted = true;
            if (self.form.$valid && !self.entity.$$__submitting) {

                var email = self.form.conta.$viewValue;

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

        self.onSubmitSuccess = $scope.$on("atendimento_cliente_usuarios_submitted", function (event, args) {
            var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
            toaster.pop({
                type: 'success',
                title: 'Sucesso ao ' + acao + ' Usuário!'
            });
            $state.go('atendimento_cliente_usuarios', self.constructors);
        });

        self.onSubmitError = $scope.$on("atendimento_cliente_usuarios_submit_error", function (event, args) {
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
            toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir Usuário!'
            });
            entityService.delete($stateParams['clientefuncao'], force);
        };

        self.onDeleteSuccess = $scope.$on("atendimento_cliente_usuarios_deleted", function (event, args) {
            $state.go('atendimento_cliente_usuarios', angular.extend(self.constructors));
        });

        self.onDeleteError = $scope.$on("atendimento_cliente_usuarios_delete_error", function (event, args) {
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
