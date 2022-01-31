import { DesfazerContaPagarModalController } from './modal-desfazer/desfazer-conta-pagar-modal.controller';
import angular = require('angular');
import { ContaEmprestimoModalController } from './modal-conta-emprestimo/conta-emprestimo-modal.controller';
import { ContaEmprestimoModalService } from './modal-conta-emprestimo/conta-emprestimo-modal.service';
import { DesfazerContaPagarModalService } from './modal-desfazer/desfazer-conta-pagar-modal.service';
import { CrmAtcContaPagarService } from './factory';
import { CrmAtcContaPagarController } from './crmatccontapagar.controller';

export const CrmAtcContaPagarModule = angular
  .module('crmAtcContaPagarModule', [])
  .controller('desfazerContaPagarModalController', DesfazerContaPagarModalController)
  .service('desfazerContaPagarModalService', DesfazerContaPagarModalService)
  .controller('contaEmprestimoModalController', ContaEmprestimoModalController)
  .service('contaEmprestimoModalService', ContaEmprestimoModalService)
  .service('crmAtcContaPagarService', CrmAtcContaPagarService)
  .controller('crmAtcContaPagarController', CrmAtcContaPagarController).name;
