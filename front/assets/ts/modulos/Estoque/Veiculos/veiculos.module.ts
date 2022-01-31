
import * as angular from 'angular';

import { EstoqueVeiculosService } from './veiculos.service';

export const EstoqueVeiculosModule = angular
    .module('estoqueVeiculosModule', ['ui.router.state'])
    .service('estoqueVeiculosService', EstoqueVeiculosService)
    .name;
