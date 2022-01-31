
import * as angular from 'angular';

import { EstoqueSaldoItensLocaisService } from './estoque-saldo-itens-locais.service';

export const EstoqueSaldoItensLocaisModule = angular.module('estoqueSaldoItensLocaisModule', ['ui.router.state'])
    .service('estoqueSaldoItensLocaisService', EstoqueSaldoItensLocaisService).name;
