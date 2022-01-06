angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['$http', 'nsjRouting', '$rootScope', function ($http, nsjRouting, $rootScope) {
                            var request = {
                                'method': 'GET',
                                'url': nsjRouting.generate('atendimento_admin_customizacoes_portal_get', {}, false)
                            };
                            
                            if (!$rootScope.customizacoes) {
                                $http(request).then(function (response) {

                                    $rootScope.customizacoes = response.data;
                                    return response.data;
                                });
                            } else {
                                return $rootScope.customizacoes;
                            }
                        }]
                };
                $stateProvider
                        .state('atendimento_admin_configuracoes', {
                            abstract: true,
                            url: '/configuracoes',
                            templateUrl: nsjRoutingProvider.$get().generate('template_configuracoes_index'),
                        })
                        .state('atendimento_admin_configuracoes_list', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "",
                            templateUrl: nsjRoutingProvider.$get().generate('template_configuracoes_list'),
                            controller: 'ClienteConfiguracoesCtrl',
                            controllerAs: 'tndmnt_clnt_cnfgrcs_lst_cntrllr'
                        })
                        .state('atendimento_admin_configuracoes_portal', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "/portal/configuracoes",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_configuracoes_portal_template_form'),
                            controller: 'AdminConfiguracoesPortalFormCtrl',
                            controllerAs: 'tndmnt_dmn_cnfgrcs_prtl_frm_cntrllr'
                        })
                        .state('atendimento_admin_customizacoes_portal', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "/portal/customizacoes",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_customizacoes_portal_template_form'),
                            controller: 'AdminCustomizacoesPortalFormCtrl',
                            controllerAs: 'tndmnt_dmn_cstmzcs_prtl_frm_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_admin_configuracoes_convites', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "/convites",
                            templateUrl: nsjRoutingProvider.$get().generate('template_configuracoes_convites'),
                            controller: 'AdminConfiguracoesConvitesFormCtrl',
                            controllerAs: 'tndmnt_dmn_cnfgrcs_cnvts_frm_cntrllr'
                        })
                        .state('atendimento_admin_configuracoes_titulos', {
                            url: "/titulos",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_configuracoes_titulos_template_form'),
                            controller: 'AdminConfiguracoesTitulosFormCtrl',
                            controllerAs: 'tndmnt_dmn_cnfgrcs_ttls_frm_cntrllr'
                        })
                        .state('atendimento_configuracoes_usuariosdisponibilidades', {
                            url: "/usuariosdisponibilidades/configuracoes",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_usuariosdisponibilidades_configuracoes_template_form'),
                            controller: 'AdminConfiguracoesUsuariosDisponibilidadesFormCtrl',
                            controllerAs: 'tndmnt_dmn_cnfgrcs_srs_dspnbldds_frm_cntrllr'
                        })
                        .state('atendimento_admin_configuracoes_termo', {
                            url: "/termo",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_configuracoes_termo_template_form'),
                            controller: 'AdminConfiguracoesTermoFormCtrl',
                            controllerAs: 'tndmnt_dmn_cnfgrcs_trm_frm_cntrllr'
                        })
                        ;
            }])
        .controller('ClienteConfiguracoesCtrl', ['$scope', '$controller', '$http', '$stateParams', 'nsjRouting', 'PageTitle',
            function ($scope, $controller, $http, $stateParams, nsjRouting, PageTitle) {
                PageTitle.setTitle("Configurações");

                var self = this;
                self.gerais = [{
                        'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-customizacao-portal.png',
                        'titulo': 'Portal',
                        'descricao': 'Deixe seu portal com a cara da sua empresa. Escolha os itens que deverão aparecer na tela inicial e personalize seus textos de chamada',
                        'sub': [
                            {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-configuracoes-gerais.png',
                                'titulo': 'Configurações Gerais',
                                'descricao': 'Configure o seu portal',
                                'link': 'atendimento_admin_configuracoes_portal'
                            },
                            {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-configuracoes-gerais.png',
                                'titulo': 'Customizações',
                                'descricao': 'Personalize as informações de entrada de seu portal',
                                'link': 'atendimento_admin_customizacoes_portal'
                            }]
                    }, {
                        'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-chamados.png',
                        'titulo': 'Chamados',
                        'descricao': 'Configure e customize seus chamados e atribua regras de negócio de acordo com as suas necessidades',
                        'sub': [{
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-filas.png',
                                'titulo': 'Filas de Chamados',
                                'descricao': 'Crie novas filas de chamados e gerencie as já existentes',
                                'link': 'servicos_atendimentosfilas'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-campos-customizados.png',
                                'titulo': 'Campos Customizados',
                                'descricao': 'Adicione e gerencie campos customizados aos seus chamados',
                                'link': 'servicos_atendimentoscamposcustomizados'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-regras.png',
                                'titulo': 'Regras',
                                'descricao': 'Crie e gerencie regras de chamados',
                                'link': 'servicos_atendimentosregras'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-enderecos-email.png',
                                'titulo': 'Endereços de e-mail',
                                'descricao': 'Gerencie os endereços de e-mail para recebimento de notificações',
                                'link': 'atendimento_admin_enderecosemails'
                            }]
                    }, {
                        'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-disponibilidade.png',
                        'titulo': 'Módulo Disponibilidade do Atendente',
                        'descricao': 'Gerencia se usuários estão disponíveis/indisponíveis em relações com chamados e notificações',
                        'sub': [{
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-customizacao-portal.png',
                                'titulo': 'Configurações',
                                'descricao': 'Configurações gerais do módulo',
                                'link': 'atendimento_configuracoes_usuariosdisponibilidades'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-usuario.png',
                                'titulo': 'Disponibilidade',
                                'descricao': 'Gerencia se atendentes estão disponíveis ou indisponíveis',
                                'link': 'atendimento_usuariosdisponibilidades_lista'
                            }]
                    },
                    {
                        'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-calendario-horario.png',
                        'titulo': 'Horários de Atendimento e Feriados',
                        'descricao': '',
                        'sub': [{
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-horas.png',
                                'titulo': 'Horários de Atendimento',
                                'descricao': 'Habilite e escolha um fuso horário para o funcionamento do Atendimento Web',
                                'link': 'atendimento_horariosatendimento'
                            },{
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-feriados.png',
                                'titulo': 'Feriados',
                                'descricao': 'Adicione feriados para notificar os clientes que o atendimento estará suspenso numa data',
                                'link': 'atendimento_feriados'
                            }]
                    },
                    {
                        'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-operacoes.png',
                        'titulo': 'Mais Configurações',
                        'descricao': 'Outras configurações do sistema',

                        'sub': [{
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-categorias-artigo.png',
                                'titulo': 'Categorias de Artigos',
                                'descricao': 'Crie e gerencie as categorias dos artigos da Base de conhecimento',
                                'link': 'atendimento_categorias'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-avisos-cliente.png',
                                'titulo': 'Avisos de Cliente',
                                'descricao': 'Gerencie os tipos de avisos que serão exibidos por cliente',
                                'link': 'atendimento_admin_flagcliente'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-equipes.png',
                                'titulo': 'Equipes',
                                'descricao': 'Gerencie as equipes de trabalho da sua empresa',
                                'link': 'atendimento_equipes'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-titulos.png',
                                'titulo': 'Boletos e Faturas',
                                'descricao': 'Configurações sobre a exibição dos boletos e faturas para os clientes',
                                'link': 'atendimento_admin_configuracoes_titulos'
                            }, {
                                'icon': 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSJMYXllciAxIiBpZD0iTGF5ZXJfMSIgdmlld0JveD0iMCAwIDY0IDY0IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDpub25lO3N0cm9rZTojMzdhODQ5O3N0cm9rZS1saW5lY2FwOnJvdW5kO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2Utd2lkdGg6MnB4O308L3N0eWxlPjwvZGVmcz48dGl0bGUvPjxwb2x5bGluZSBjbGFzcz0iY2xzLTEiIHBvaW50cz0iMjQuMDQgMzcuNTMgMzAuMDEgNDMuNSA0MS45NyAzMS41NSIvPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTUyLDE5VjU0YTQsNCwwLDAsMS00LDRIMTZhNCw0LDAsMCwxLTQtNFYxMWE0LDQsMCwwLDEsNC00SDQ0bDgsOEg0NmEyLDIsMCwwLDEtMi0yVjciLz48L3N2Zz4=',
                                'titulo': 'Termo de serviço',
                                'descricao': 'Crie termo de serviço para acesso a área de clientes',
                                'link': 'atendimento_admin_configuracoes_termo'
                            }, {
                                'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-sla.png',
                                'titulo': 'SLAs',
                                'descricao': 'Cadastro de SLAs',
                                'link': 'atendimento_admin_slas'
                            }]
                    }
                ];

                if (nsj.globals.getInstance().get('DISPONIBILIZAR_ARQUIVOS') == '1') {
                    self.gerais[self.gerais.length - 1].sub.push({
                        'icon': '//s3-us-west-2.amazonaws.com/static.nasajon/img/atendimento/icos-configs/ico-arquivos.png',
                        'titulo': 'Arquivos',
                        'descricao': 'Administração de arquivos a serem disponibilizados aos clientes',
                        'link': 'atendimento_admin_arquivos'
                    });
                }
            }])
        .controller('AdminConfiguracoesPortalFormCtrl', ['$http', '$state', 'nsjRouting', '$rootScope',
            function ($http, $state, nsjRouting, $rootScope) {
                var self = this;
                self.entity = {};
                self.busy = true;
                self.submitted = true;

                self.submit = function (form, event) {
                    if (form.$valid) {
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_configuracoes_portal_put', {}, false),
                            data: self.entity
                        }).then(function (response) {
                            $state.go('atendimento_admin_configuracoes_list');
                        }).catch(function (response) {
                            alert(response.data.erro);
                        });
                    }
                }

                $http.get(nsjRouting.generate('atendimento_admin_configuracoes_portal_get', {}, false))
                        .then(function (response) {
                            self.entity = response.data;
                        })
                        .finally(function () {
                            self.busy = false;
                        });

            }])

        .controller('AdminCustomizacoesPortalFormCtrl', ['$http', '$state', 'nsjRouting', 'toaster', '$rootScope',
            function ($http, $state, nsjRouting, toaster, $rootScope) {
                var self = this;
                self.entity = {};
                self.busy = true;
                self.submitted = true;

                self.submit = function (form, event) {
                    if (form.$valid) {
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_customizacoes_portal_put', {}, false),
                            data: self.entity
                        }).then(function (response) {
                            $state.go('atendimento_admin_configuracoes_list');
                        }).catch(function (response) {
                            alert(response.data.erro);
                        });
                    }
                };

                if (!$rootScope.customizacoes) {
                    $http.get(nsjRouting.generate('atendimento_admin_customizacoes_portal_get', {}, false))
                        .then(function (response) {
                            self.entity = response.data;

                            $rootScope.customizacoes = response.data;
                        })
                        .finally(function () {
                            self.busy = false;
                        });
                } else {
                    self.entity = $rootScope.customizacoes;
                }
            }])


        .controller('ClienteConfiguracoesSolicitacoesCtrl', ['$scope', '$controller', '$http', '$stateParams', 'nsjRouting', function ($scope, $controller, $http, $stateParams, nsjRouting) {
            }])
        .controller("AdminConfiguracoesConvitesFormCtrl", ['$rootScope', '$scope', '$controller', '$http', '$stateParams', '$state', 'nsjRouting',
            function ($rootScope, $scope, $controller, $http, $stateParams, $state, nsjRouting) {
                var self = this;
                self.entity = {};
                self.busy = true;
                self.submitted = true;
                self.submit = function (form, event) {
                    if (form.$valid) {
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_configuracoes_put', {}, false),
                            data: self.entity
                        }).then(function (response) {
                            $state.go('atendimento_admin_configuracoes_list');
                        }).catch(function (response) {
                            alert(response.data.erro);
                        });
                    }
                }
                $http.get(nsjRouting.generate('atendimento_admin_configuracoes_get', {}, false))
                        .then(function (response) {
                            self.entity = response.data;
                        })
                        .finally(function () {
                            self.busy = false;
                        });
            }])
        .controller("AdminConfiguracoesConvitesFormCtrl", ['$http', 'nsjRouting', 'NsClientes', function ($http, nsjRouting, NsClientes) {
                var self = this;
                self.clientes = NsClientes.load();
            }])
        .controller("AdminConfiguracoesTitulosFormCtrl", ['$rootScope', '$scope', '$controller', '$http', '$stateParams', '$state', 'nsjRouting', function ($rootScope, $scope, $controller, $http, $stateParams, $state, nsjRouting) {

                var self = this;
                self.entity = {};
                self.busy = true;

                self.isBusy = function () {
                    return self.busy;
                };
                
                self.situacoesTitulo = [
                    {nome:"Aberto",     id: 0},
                    {nome:"Quitado",    id: 1},
                    {nome:"Em débito",  id: 2},
                    {nome:"Cancelado",  id: 3},
                    {nome:"Protestando",id: 4},
                    {nome:"Estornado",  id: 5},
                    {nome:"Agendado",   id: 6},
                    {nome:"Suspenso",   id: 7}
                ]
                    
                self.submitted = true;
                self.submit = function (form, event) {
                    
                    var array = [];
                    for (var i = 0; i < self.situacoesTitulo.length; i++)
                        if (self.situacoesTitulo[i].marcado) array.push(self.situacoesTitulo[i].id);
                    self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES = array;

                    if (!Array.isArray(self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES)) {
                        var situacao = self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES;
                        self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES = [situacao];
                    }

                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_configuracoes_titulos_put', {}, false),
                        data: self.entity
                    }).then(function (response) {
                        $state.go('atendimento_admin_configuracoes_list');
                    }).catch(function (response) {
                        alert(response.data.erro);
                    });
                }
                $http.get(nsjRouting.generate('atendimento_configuracoes_titulos_get', {}, false))
                        .then(function (response) {
                            self.entity = response.data;
                            try {
                                var jsituacoes = JSON.parse(self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES);
                                self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES = jsituacoes;
                            } catch (e) {}
                            for (var i = 0; i < self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES.length; i++) {
                                var idx = self.entity.TITULOS_EXIBIR_SEGUINTES_SITUACOES[i]
                                self.situacoesTitulo[idx].marcado = true;
                            }
                            self.entity.TITULOS_EXIBIR_PARA_CLIENTE_BOLETO == 1 ? self.entity.TITULOS_EXIBIR_PARA_CLIENTE_BOLETO = true : self.entity.TITULOS_EXIBIR_PARA_CLIENTE_BOLETO = false;
                            self.entity.TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL == 1 ? self.entity.TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL = true : self.entity.TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL = false;
                        })
                        .finally(function () {
                            self.busy = false;
                        });

            }])
        .controller("AdminConfiguracoesUsuariosDisponibilidadesFormCtrl", ['$rootScope', '$scope', '$controller', '$http', '$stateParams', '$state', 'nsjRouting', function ($rootScope, $scope, $controller, $http, $stateParams, $state, nsjRouting) {

                var self = this;
                self.entity = {};
                self.busy = true;

                self.isBusy = function () {
                    return self.busy;
                };

                self.submitted = true;
                self.submit = function (form, event) {

                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_usuariosdisponibilidades_configuracoes_put', {}, false),
                        data: self.entity
                    }).then(function (response) {
                        $state.go('atendimento_admin_configuracoes_list');
                    }).catch(function (response) {
                        alert(response.data.erro);
                    });

                }
                $http.get(nsjRouting.generate('atendimento_usuariosdisponibilidades_configuracoes_get', {}, false))
                        .then(function (response) {
                            self.entity = response.data;
                        })
                        .finally(function () {
                            self.busy = false;
                        });

            }])
          .controller('AdminConfiguracoesTermoFormCtrl', ['$http', '$state', 'nsjRouting',
            function ($http, $state, nsjRouting) {
                var self = this;
                self.entity = {};
                self.busy = true;
                self.submitted = true;

                self.submit = function (form, event) {
                    if (form.$valid) {
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_configuracoes_termo_put', {}, false),
                            data: self.entity
                        }).then(function (response) {
                            $state.go('atendimento_admin_configuracoes_list');
                        }).catch(function (response) {
                            alert(response.data.erro);
                        });
                    }
                }
                $http.get(nsjRouting.generate('atendimento_admin_configuracoes_termo_get', {}, false))
                        .then(function (response) {
                            self.entity = response.data;
                        })
                        .finally(function () {
                            self.busy = false;
                        });
            }])
        ;