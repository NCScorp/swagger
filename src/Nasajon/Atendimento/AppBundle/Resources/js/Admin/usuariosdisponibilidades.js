angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                $stateProvider
                        .state('atendimento_usuariosdisponibilidades_lista', {
                            url: "/usuariosdisponibilidades",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_usuariosdisponibilidades_lista'),
                            controller: 'AtendimentoUsuariosDisponibilidadesListaCntrllr',
                            controllerAs: 'tndmnt_srs_dspnbldds_lst_cntrllr'
                        }).state('atendimento_usuariosdisponibilidades_historico', {
                            url: "/usuariosdisponibilidades/:email/historico",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_usuariosdisponibilidades_historico_template'),
                            controller: 'AtendimentoUsuariosDisponibilidadesHistoricoController',
                            controllerAs: 'tndmnt_srs_dspnbldds_hstrc_cntrllr',
                            params: { nome: null, email: null, equipe: null, funcao: null }
                        });
            }])
        .controller('AtendimentoUsuariosDisponibilidadesListaCntrllr', ['$scope', '$controller', '$http', '$stateParams', '$state', '$window', 'nsjRouting', 'toaster', 'UsuarioLogado', function ($scope, $controller, $http, $stateParams, $state, $window, nsjRouting, toaster, UsuarioLogado) {

            var self = this;
            self.contas = [];
            self.busy = false;
            self.usuariosIndisponiveis = [];
            self.usuarioLogado = UsuarioLogado.getUsuario();
            
            self.isBusy = function () {
                return self.busy;
            };

            self.abrirHistorico = function (conta) {
                $state.go(
                    'atendimento_usuariosdisponibilidades_historico', 
                    angular.extend({
                        nome: conta.nome, 
                        email: conta.email, 
                        equipe: conta.equipe, 
                        funcao: conta.usuariotipo
                    })
                );
            };

            self.setStatus = function (usuario) {
                $http({
                    method: "PUT",
                    url: nsjRouting.generate('atendimento_usuariosdisponibilidades_alterar', false, false),
                    data: {
                        usuario: usuario.email,
                        status: usuario.status
                    }
                }).then(function (response) {
                    toaster.pop({
                        type: 'success',
                        title: 'Disponibilidade do usuário alterada com sucesso!'
                    });

                    if (usuario.email === self.usuarioLogado.email) {
                        setTimeout(function() {
                            $window.location.reload();
                        }, 2000);
                    }
                }).catch(function (response) {
                    alert(response.data.erro);
                });
            };
            
            if (self.busy === false) {
                self.busy = true;
                
                $http.get(nsjRouting.generate('atendimento_admin_contas_index', {}, false)).then(function (response) {
                    for (var i in response.data) {
                        var conta = {
                            'nome': response.data[i].nome,
                            'email': response.data[i].email,
                            'status': true
                        };
                        self.contas.push(conta);
                    }

                    $http.get(nsjRouting.generate('atendimento_usuariosdisponibilidades_lista_indisponiveis', {}, false)).then(function (response) {
                        for (var i in response.data) {
                            for (var j in self.contas) {
                                if (self.contas[j]['email'] == response.data[i]) {
                                    self.contas[j]['status'] = false;
                                }
                            }
                        }

                        $http.get(nsjRouting.generate('atendimento_equipes_list_usuarios_alocados_equipe', {}, false)).then(function (response) {
                            for (var i in response.data) {
                                for (var j in self.contas) {
                                    if (self.contas[j]['email'] == response.data[i].usuario) {
                                        self.contas[j]['equipe'] = response.data[i].equipe;
                                        self.contas[j]['usuariotipo'] = (response.data[i].usuariotipo == 'U' ? 'Usuário' : 'Gerente');
                                    }
                                }
                            }
                        }).finally(function () {
                            self.busy = false;
                        });
                    });

                });
            }
        }]).controller('AtendimentoUsuariosDisponibilidadesHistoricoController', ['$stateParams', '$http', 'nsjRouting', function ($stateParams, $http, nsjRouting) {
            var self = this;
            self.nome = $stateParams['nome'];
            self.email = $stateParams['email'];
            self.equipe = $stateParams['equipe'];
            self.funcao = $stateParams['funcao'];
            self.busy = false;
            self.finished = false;
            self.after = "";
            self.historicos = [];
            
            function calcMins(dt2, dt1) {
                var diff =(dt2.getTime() - dt1.getTime()) / 1000;
                diff /= 60;
                return Math.abs(Math.round(diff));
            }
            
            function calcularDados(dados) {
                return dados.map(function (historicos) {
                    historicos.created_at = new Date(historicos.created_at);
                    if(historicos.updated_at !== null && historicos.updated_at !== undefined) {
                        historicos.updated_at = new Date(historicos.updated_at);
                        var totalMins = calcMins(historicos.updated_at, historicos.created_at);
                        var dias = Math.floor(totalMins/1440);
                        var horas = Math.floor((totalMins - (dias * 1440))/60);
                        var minutos = (totalMins - ((dias * 1440) + (horas * 60)));

                        historicos.intervalo = {
                            dias: dias,
                            horas: horas,
                            minutos: minutos
                        };
                    } 
                    
                    return historicos;
                }); 
            }
            
            $http({ method: "GET", url: nsjRouting.generate('atendimento_usuariosdisponibilidades_historico', {'email': $stateParams['email']}, false)}).then(function (response) {
                
                if (response.data.length < 30) {
                    self.finished = true;
                } else {
                    self.after = response.data[response.data.length - 1]['created_at'];
                }
                
                self.historicos = calcularDados(response.data);
            });
            
            self.loadMore = function () {
                if (!self.busy && !self.finished) {
                    self.busy = true;
                    
                    $http({ method: "GET", url: nsjRouting.generate('atendimento_usuariosdisponibilidades_historico', {'email': $stateParams['email'], 'created_at': self.after }, false)}).then(function (response) {                        
                        var data = calcularDados(response.data);
                        
                        for (var i = 0; i < data.length; i++) {
                            self.historicos.push(data[i]);
                        }
                        
                        if (response.data.length < 30) {
                            self.finished = true;
                        } else {
                            self.after = response.data[response.data.length - 1]['created_at'];
                        }
                    }).finally(function () {
                        self.busy = false;
                    });
                }
            };
        }]);