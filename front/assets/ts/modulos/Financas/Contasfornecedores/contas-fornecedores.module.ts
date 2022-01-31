import angular from "angular";
import { financasContasfornecedoresShowShow, financasContasfornecedoresDefault } from "./components";
import { FinancasContasfornecedoresDefaultController } from "./default.form";
import { FinancasContasfornecedores } from "./factory";
import { FinancasContasfornecedoresFormModalController, FinancasContasfornecedoresFormService } from "./modal";
import { FinancasContasfornecedoresFormModalShowController, FinancasContasfornecedoresFormShowService } from "./modal.show";
import { FinancasContasfornecedoresShowShowController } from "./show.form.show";

export const ContasfornecedoresModule = angular
    .module('ContasfornecedoresModule', [])
    .controller('FinancasContasfornecedoresDefaultController', FinancasContasfornecedoresDefaultController)
    .service('FinancasContasfornecedores', FinancasContasfornecedores)
    .controller('FinancasContasfornecedoresFormModalShowController', FinancasContasfornecedoresFormModalShowController)
    .controller('FinancasContasfornecedoresFormModalController', FinancasContasfornecedoresFormModalController)
    .controller('FinancasContasfornecedoresShowShowController', FinancasContasfornecedoresShowShowController)
    .service('FinancasContasfornecedoresFormService', FinancasContasfornecedoresFormService)
    .service('FinancasContasfornecedoresFormShowService', FinancasContasfornecedoresFormShowService)
    .component('financasContasfornecedoresShowShow', financasContasfornecedoresShowShow)
    .component('financasContasfornecedoresDefault', financasContasfornecedoresDefault)
    .name