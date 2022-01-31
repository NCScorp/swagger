
import * as angular from 'angular';

import { EstoqueLocaisService } from './estoque-locais.service';

export const EstoqueLocaisModule = angular.module('estoqueLocaisModule', ['ui.router.state'])
    .service('estoqueLocaisService', EstoqueLocaisService).name;
