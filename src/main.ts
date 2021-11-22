import * as angular from 'angular';
import configJson from './config/config.json';


import { NsjCore } from './app/core/nsj-core';
import { Usuario } from './app/old-services/usuario.service';
import {app} from './app/app.module'

class Main {
    constructor() {
        NsjCore.bootstrap(configJson,this.bootstrap);
    }

    private bootstrap(){
        /*
        TODO: Remover e injetar o instância de do usuário carregado pelo core .
        header Nasajon-ui necessita de um service com uma function getProfile retornando dados do profile em uma promisse 
        */
        angular.module(app).service('Usuario', Usuario);

        angular.bootstrap(document, [app]);
    }
}

new Main();
