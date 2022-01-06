(function () {
    'use strict';

    angular.module('nsjRedux', [])
            .factory('rootReducerAdmin', ['artigosAdminReducer', function (artigosAdminReducer) {
                    return Redux.combineReducers({
                        artigos: artigosAdminReducer
                    });
                }])
            .factory('rootReducerCliente', ['artigosClienteReducer', function (artigosClienteReducer) {
                    return Redux.combineReducers({
                        artigos: artigosClienteReducer
                    });
                }]);
})();
