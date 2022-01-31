import angular from "angular";
import { nsClientesdocumentosDefaultShow, nsClientesdocumentosDefault } from "./components";
import { NsClientesdocumentosDefaultController } from "./default.form";
import { NsClientesdocumentosDefaultShowController } from "./default.form.show";
import { NsClientesdocumentos } from "./factory";
import { NsClientesdocumentosFormModalController, NsClientesdocumentosFormService } from "./modal";
import { NsClientesdocumentosFormModalShowController, NsClientesdocumentosFormShowService } from "./modal.show";

export const ClientesdocumentosModule =  angular
.module('ClientesdocumentosModule', [])
.controller('NsClientesdocumentosDefaultShowController', NsClientesdocumentosDefaultShowController)
.controller('NsClientesdocumentosDefaultController', NsClientesdocumentosDefaultController)
.service('NsClientesdocumentos', NsClientesdocumentos)
.controller('NsClientesdocumentosFormModalShowController', NsClientesdocumentosFormModalShowController)
.controller('NsClientesdocumentosFormModalController', NsClientesdocumentosFormModalController)
.service('NsClientesdocumentosFormService', NsClientesdocumentosFormService)
.service('NsClientesdocumentosFormShowService', NsClientesdocumentosFormShowService)
.component('nsClientesdocumentosDefaultShow',nsClientesdocumentosDefaultShow)
.component('nsClientesdocumentosDefault',nsClientesdocumentosDefault)
.name