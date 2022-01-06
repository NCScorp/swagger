(function () {
    angular.module('nsjRedux')
            .factory('artigosClienteActions', ['AtendimentoClienteArtigos', '$http', function (Artigos, $http) {
                    return {
                        carregaTopo: carregaTopo,
                        carrega: carrega,
                        busca: busca,
                        mostra: mostra,
                        carregaMais: carregaMais
                    };

                    function carregaTopo() {
                        Artigos.after = '';                        
                        if (!Artigos.busyList && !Artigos.finished) {                            
                            return {
                                type: 'CARREGA_ARTIGOS_TOPO',
                                payload: {
                                    promise: Artigos._load(angular.extend(Artigos.constructors, {'fixarnotopo': '1'}), Artigos.after, Artigos.filter)
                                }
                            };
                        }
                    }

                    function carrega() {
                        Artigos.after = '';
                        if (!Artigos.busyList && !Artigos.finished) {
                            return {
                                type: 'CARREGA_ARTIGOS',
                                payload: {
                                    promise: Artigos._load(angular.extend(Artigos.constructors, {'fixarnotopo': '0'}), Artigos.after, Artigos.filter)
                                }
                            };
                        }
                    }

                    function busca(a, b, c) {
                        if ($http.pendingRequests.length > 0) {
                            $http.pendingRequests.forEach(function (request) {
                                Artigos.loading_deferred.resolve();
                            });
                        }
                        
                        Artigos.finished = false;
                        Artigos.after = "";
                        Artigos.loaded = false;
                        Artigos.entities.length = 0;

                        if (a == 'filterfield' || Artigos.filter.key == '') {
                            return carregaTopo();
                        } else {
                            if (Artigos.busy) {
                                Artigos.loading_deferred.resolve();
                            }
                            
                            return {
                                type: 'BUSCA_ARTIGOS',
                                payload: {
                                    promise: Artigos._load(angular.extend(Artigos.constructors), Artigos.after, Artigos.filter)
                                }
                            };
                        }
                    }

                    function mostra(id) {
                        if (!Artigos.busyShow) {
                            return {
                                type: 'MOSTRA_ARTIGO',
                                payload: {
                                    promise: Artigos.get(id)
                                }
                            };
                        }
                    }
                    
                    function carregaMais() {
                        return {
                            type: 'CARREGA_MAIS_ARTIGOS',
                            payload: {
                                promise: Artigos._load(Artigos.constructors, Artigos.after, Artigos.filter)
                            }
                        };
                    }

                }]);
})();