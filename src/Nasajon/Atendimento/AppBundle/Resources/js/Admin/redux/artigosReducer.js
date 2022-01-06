(function () {
    angular.module('nsjRedux')
            .provider('artigosAdminReducer', [function () {
                    var initialState = {
                        artigo: {},
                        artigostopo: [],
                        artigos: [],
                        lastaction: null,
                        busyList: false,
                        busyShow: false,
                        finished: false,
                        constructors: {}
                    };

                    this.$get = function () {
                        return reducer;
                    };

                    function reducer(state, action) {
                        if (angular.isUndefined(state)) {
                            return angular.copy(initialState);
                        }

                        state.lastaction = action.type;

                        if (action.type.indexOf('_REJEITADO') > -1) {
                            state.busyList = false;
                            state.busyShow = false;
                        } else {
                            if (action.type.indexOf('_COMPLETO') > -1) {
                                state.busyList = false;
                                state.busyShow = false;
                                if (action.payload.length === 60)
                                    state.finished = false;
                                else
                                    state.finished = true;

                                if (action.payload.length > 0)
                                    state.after = action.payload[action.payload.length - 1]['lastupdate'];
                            }

                            switch (action.type) {
                                case 'CRIA_ARTIGO_PENDENTE':
                                case 'CARREGA_ARTIGOS_TOPO_PENDENTE':
                                case 'CARREGA_ARTIGOS_PENDENTE':
                                case 'CARREGA_MAIS_ARTIGOS_PENDENTE':
                                case 'ATUALIZA_ARTIGO_PENDENTE':
                                case 'DELETA_ARTIGO_PENDENTE':
                                case 'BUSCA_ARTIGOS_PENDENTE':
                                    state.busyList = true;
                                    break;
                                case 'MOSTRA_ARTIGO_PENDENTE':
                                    state.busyShow = true;
                                    break;
                                case 'CRIA_ARTIGO_COMPLETO':
                                    state.artigo = angular.copy(action.payload.data);
                                    if (action.extra.addToList)
                                        state.artigos.unshift(angular.copy(state.artigo));
                                    break;
                                case 'CARREGA_ARTIGOS_TOPO_COMPLETO':
                                    state.artigostopo = angular.copy(action.payload);
                                    state.artigos = angular.copy(action.payload);
                                    break;
                                case 'CARREGA_ARTIGOS_COMPLETO':
                                    state.artigos = angular.copy(state.artigostopo).concat(angular.copy(action.payload));
                                    break;
                                case 'BUSCA_ARTIGOS_COMPLETO':
                                    state.artigos = angular.copy(action.payload);
                                    break;
                                case 'CARREGA_MAIS_ARTIGOS_COMPLETO':
                                    state.artigos = angular.copy(state.artigos).concat(angular.copy(action.payload));
                                    break;
                                case 'MOSTRA_ARTIGO_COMPLETO':
                                    state.artigo = angular.copy(action.payload);
                                    break;
                                case 'ATUALIZA_ARTIGO_COMPLETO':
                                    state.artigo = angular.copy(action.payload.data);
                                    state.artigos = state.artigos.map(function (s) {
                                        return s.artigo === action.payload.data.artigo ? s = angular.copy(action.payload.data) : s;
                                    });
                                    break;
                                case 'DELETA_ARTIGO_COMPLETO':
                                    var id = action.payload.config.url.match(/(?:artigos\/)(.+)(?:\?)/)[1];
                                    state.artigo = {};
                                    state.artigos = state.artigos.filter(function (s) {
                                        return s.artigo !== id;
                                    });
                                    break;
                                case 'SET_STATUS_ARTIGO_COMPLETO':
                                    var id = action.payload.config.url.match(/(?:artigos\/)(.+)(?:\/)/)[1];
                                    state.artigos = state.artigos.map(function (s) {
                                        if (s.artigo === id) {
                                            s.status = !s.status;
                                            state.artigo.status = angular.copy(s.status);
                                        }

                                        return s;
                                    });
                                    break;
                            }
                        }

                        return state;
                    }
                }]);
})();