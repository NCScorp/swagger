import angular from "angular";
import { nsCidadesinfofunerariasfornecedoresShowShow, nsCidadesinfofunerariasfornecedoresDefault } from "./components";
import { NsCidadesinfofunerariasfornecedoresDefaultController } from "./default.form";
import { NsCidadesinfofunerariasfornecedores } from "./factory";
import { NsCidadesinfofunerariasfornecedoresFormModalController, NsCidadesinfofunerariasfornecedoresFormService } from "./modal";
import { NsCidadesinfofunerariasfornecedoresFormModalShowController, NsCidadesinfofunerariasfornecedoresFormShowService } from "./modal.show";
import { NsCidadesinfofunerariasfornecedoresShowShowController } from "./show.form.show";

export const CidadesinfofunerariasfornecedoresModule = angular
    .module('CidadesinfofunerariasfornecedoresModule', [])
    .controller('NsCidadesinfofunerariasfornecedoresDefaultController', NsCidadesinfofunerariasfornecedoresDefaultController)
    .service('NsCidadesinfofunerariasfornecedores', NsCidadesinfofunerariasfornecedores)
    .controller('NsCidadesinfofunerariasfornecedoresFormModalShowController', NsCidadesinfofunerariasfornecedoresFormModalShowController)
    .controller('NsCidadesinfofunerariasfornecedoresFormModalController', NsCidadesinfofunerariasfornecedoresFormModalController)
    .controller('NsCidadesinfofunerariasfornecedoresShowShowController', NsCidadesinfofunerariasfornecedoresShowShowController)
    .service('NsCidadesinfofunerariasfornecedoresFormService', NsCidadesinfofunerariasfornecedoresFormService)
    .service('NsCidadesinfofunerariasfornecedoresFormShowService', NsCidadesinfofunerariasfornecedoresFormShowService)
    .component('nsCidadesinfofunerariasfornecedoresShowShow', nsCidadesinfofunerariasfornecedoresShowShow)
    .component('nsCidadesinfofunerariasfornecedoresDefault', nsCidadesinfofunerariasfornecedoresDefault)
    .name