
import * as angular from 'angular';

import { EstoqueItensService } from './estoque-itens.service';

export const EstoqueItensModule = angular
    .module('estoqueItensModule', ['ui.router.state'])
    .service('estoqueItensService', EstoqueItensService).name;
