(function () {
    'use strict';

    angular.module('nsjRedux')
            .factory('promiseMiddleware', ['$q', 'toaster', function ($q, toaster) {
                    return middleware;

                    function middleware(store) {
                        return function (next) {
                            return function (action) {
                                if (!actionContainsPromise(action)) {
                                    return next(action || { payload: { promise: null }, type: "" });
                                }

                                next(createAction(action, {
                                    suffix: 'PENDENTE',
                                    payload: action.payload.data
                                }));

                                // Convert to array if needed
                                if (!Array.isArray(action.payload.promise)) {
                                    action.payload.promise = [action.payload.promise];
                                }

                                // Chain all promises
                                action.payload.promise.reduce(function (promise, nextPromise) {
                                    return promise.then(function () {
                                        return nextPromise.then(function (response, x, y) {
                                            if (action.extra && action.extra.successMsg) {
                                                toaster.pop({
                                                    type: 'success',
                                                    title: action.extra.successMsg
                                                });
                                            }
                                            next(createAction(action, {
                                                suffix: 'COMPLETO',
                                                payload: response,
                                                extra: action.extra
                                            }));
                                        }, function (error) {
                                            if (action.extra && action.extra.errorMsg) {
                                                toaster.pop({
                                                    type: 'error',
                                                    title: action.extra.errorMsg
                                                });
                                            }
                                            next(createAction(action, {
                                                suffix: 'REJEITADO',
                                                payload: error,
                                                extra: action.extra,
                                                error: true
                                            }));
                                        });
                                    });
                                }, $q.resolve());
                            };
                        };
                    }

                    function createAction(baseAction, options) {
                        options = options || {};

                        var result = {
                            type: baseAction.type + '_' + options.suffix,
                            payload: options.payload,
                            extra: options.extra
                        };

                        if (baseAction.meta) {
                            result.meta = baseAction.meta;
                        }

                        if (options.error) {
                            result.error = options.error;
                        }

                        return result;
                    }

                    function actionContainsPromise(action) {
                        var payload = action ? action.payload : { promise: null };

                        if (payload !== null && typeof payload === 'object') {
                            return payload.promise && (typeof payload.promise.then === 'function' || Array.isArray(action.payload.promise));
                        }
                    }
                }]);
})();