import angular from "angular";
import { CrmAtcsFichaFinanceiraController } from "./fichafinanceira";
import { CrmAtcsFichaFinanceiraAdicionarOrcamentoController, CrmAtcsFichaFinanceiraAdicionarOrcamentoService } from "./modal-adicionar-orcamento/modal-adicionar-orcamento";
import { ModalExclusaoItemOrcamentoController, ModalExclusaoItemOrcamentoService } from "./modal-exclusao";

export const fichaFinanceiraModule = angular.module('fichaFinanceiraModule', [])
    .controller('CrmAtcsFichaFinanceiraController', CrmAtcsFichaFinanceiraController)
    .controller('CrmAtcsFichaFinanceiraAdicionarOrcamentoController', CrmAtcsFichaFinanceiraAdicionarOrcamentoController)
    .service('CrmAtcsFichaFinanceiraAdicionarOrcamentoService', CrmAtcsFichaFinanceiraAdicionarOrcamentoService)
    .service('ModalExclusaoItemOrcamentoService', ModalExclusaoItemOrcamentoService)
    .controller('ModalExclusaoItemOrcamentoController', ModalExclusaoItemOrcamentoController)
    .name