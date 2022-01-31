import angular from "angular";
import { nsFornecedoresdocumentosDefaultShow, nsFornecedoresdocumentosDefault } from "./components";
import { NsFornecedoresdocumentosDefaultController } from "./default.form";
import { NsFornecedoresdocumentosDefaultShowController } from "./default.form.show";
import { NsFornecedoresdocumentos } from "./factory";
import { NsFornecedoresdocumentosFormModalController, NsFornecedoresdocumentosFormService } from "./modal";
import { NsFornecedoresdocumentosFormModalShowController, NsFornecedoresdocumentosFormShowService } from "./modal.show";

export const FornecedoresdocumentosModule = angular
    .module('FornecedoresdocumentosModule', [])
    .controller('NsFornecedoresdocumentosDefaultShowController', NsFornecedoresdocumentosDefaultShowController)
    .controller('NsFornecedoresdocumentosDefaultController', NsFornecedoresdocumentosDefaultController)
    .service('NsFornecedoresdocumentos', NsFornecedoresdocumentos)
    .controller('NsFornecedoresdocumentosFormModalShowController', NsFornecedoresdocumentosFormModalShowController)
    .controller('NsFornecedoresdocumentosFormModalController', NsFornecedoresdocumentosFormModalController)
    .service('NsFornecedoresdocumentosFormService', NsFornecedoresdocumentosFormService)
    .service('NsFornecedoresdocumentosFormShowService', NsFornecedoresdocumentosFormShowService)
    .component('nsFornecedoresdocumentosDefaultShow', nsFornecedoresdocumentosDefaultShow)
    .component('nsFornecedoresdocumentosDefault', nsFornecedoresdocumentosDefault)
    .name