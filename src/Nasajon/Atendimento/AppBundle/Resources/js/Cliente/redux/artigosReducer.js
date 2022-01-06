(function () {
    angular.module('nsjRedux')
            .provider('artigosClienteReducer', [function () {
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
                                case 'CARREGA_ARTIGOS_TOPO_PENDENTE':
                                case 'CARREGA_ARTIGOS_PENDENTE':
                                case 'BUSCA_ARTIGOS_PENDENTE':
                                case 'CARREGA_MAIS_ARTIGOS_PENDENTE':
                                    state.busyList = true;
                                    break;
                                case 'MOSTRA_ARTIGO_PENDENTE':
                                    state.busyShow = true;
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
                            }
                        }

                        return state;
                    }
                }]);
})();