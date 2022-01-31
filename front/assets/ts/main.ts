import angular from 'angular';
import configJson from './../../config/rotasSistema.json';
import './app_index';

import { NsjCore } from '@nsj/core';
import { app } from './app.module'
import { carregarRotasSistema } from './inicializacao/inicializacao-rotas';

class Main {

    constructor() {
        this._init()
    }

    private _init() {
        NsjCore.bootstrap({
            config: configJson,
            bootstrapFn: this.bootstrap,
            /**@todo - Remover quando todas as rotas estiver no ErpApi*/
            routesFactory: () => carregarRotasSistema()
        })
    }

    private bootstrap() {

        angular.bootstrap(document, [app]);
    }
}

new Main();