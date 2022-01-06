(function () {
    angular.module('nsjRedux')
            .factory('artigosAdminActions', ['AtendimentoAdminArtigos', 'AtendimentoAdminArtigosOver', '$http', function (Artigos, ArtigosOver, $http) {
                    return {
                        cria: cria,
                        carregaTopo: carregaTopo,
                        carrega: carrega,
                        carregaMais: carregaMais,
                        busca: busca,
                        mostra: mostra,
                        atualiza: atualiza,
                        deleta: deleta,
                        setStatus: setStatus
                    };
                    function cria(artigo, secao) {
                        return {
                            type: 'CRIA_ARTIGO',
                            payload: {
                                promise: Artigos.save(artigo)
                            },
                            extra: {
                                addToList: (secao === artigo.secao.categoria || !secao),
                                successMsg: "Artigo criado com sucesso",
                                errorMsg: "Erro ao criar artigo"
                            }
                        };
                    }

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
                                    promise: Artigos._load(angular.extend(Artigos.constructors), Artigos.after, Artigos.filter)
                                }
                            };
                        }
                    }

                    function busca(a, b, c) {
                        if (Artigos.filter.key === "") return
                        
                        if ($http.pendingRequests.length > 0) {
                            $http.pendingRequests.forEach(function (request) {
                                Artigos.loading_deferred.resolve();
                            });
                        }
                        
                        Artigos.finished = false;
                        Artigos.after = "";
                        Artigos.loaded = false;
                        Artigos.entities.length = 0;


                        
                        if (Artigos.busy) {
                            Artigos.loading_deferred.resolve();
                        }

                        return {
                            type: 'BUSCA_ARTIGOS',
                            payload: {
                                promise: Artigos.loadMore()
                            }
                        };
                    }

                    function carregaMais() {
                        return {
                            type: 'CARREGA_MAIS_ARTIGOS',
                            payload: {
                                promise: Artigos._load(Artigos.constructors, Artigos.after, Artigos.filter)
                            }
                        };
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

                    function atualiza(artigo) {
                        return {
                            type: 'ATUALIZA_ARTIGO',
                            payload: {
                                promise: Artigos.save(artigo)
                            },
                            extra: {
                                successMsg: "Artigo atualizado com sucesso",
                                errorMsg: "Erro ao atualizar artigo"
                            }
                        };
                    }

                    function deleta(id, force) {
                        if (typeof (id) == 'object') {
                            id = id['artigo'];
                        }
                        if (id) {
                            if (force || confirm("Tem certeza que deseja deletar?")) {
                                return {
                                    type: 'DELETA_ARTIGO',
                                    payload: {
                                        promise: ArtigosOver.delete(Artigos.constructors, id)
                                    },
                                    extra: {
                                        successMsg: "Artigo excluído com sucesso",
                                        errorMsg: "Erro ao excluir artigo"
                                    }
                                };
                            }
                        }
                    }

                    function setStatus(artigo, status) {
                        return {
                            type: 'SET_STATUS_ARTIGO',
                            payload: {
                                promise: ArtigosOver.setStatus(artigo, status)
                            },
                            extra: {
                                successMsg: "Artigo " + (artigo.status === true ? "publicado" : "despublicado") + " com sucesso!",
                                errorMsg: "Erro ao publicar artigo"
                            }
                        };
                    }
                }]);
})();
