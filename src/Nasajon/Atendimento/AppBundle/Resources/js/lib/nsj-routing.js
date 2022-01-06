angular.module('nsjRouting', [])
        .provider('nsjRouting', function () {
            var config = {
                baseParams: {
                }
            };
            if((typeof(nsj)!='undefined') && nsj.globals){
                if(nsj.globals.getInstance().get('tenant')){
                    config.baseParams['tenant'] = nsj.globals.getInstance().get('tenant');
                }
                if(nsj.globals.getInstance().get('funcao')){
                    config.baseParams['funcao'] = nsj.globals.getInstance().get('funcao');
                }
            }
            
            return {
                config: config,
                $get: function () {
                    return {
                        generate: function (route, opt_params, absolute) {
                            var params = angular.extend(angular.copy(config.baseParams), (opt_params || {}));
                            return Routing.generate(route, params, absolute);
                        }
                    };
                }
            };
        });
