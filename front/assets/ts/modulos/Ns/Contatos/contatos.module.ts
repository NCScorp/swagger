import angular from "angular";
import { nsContatosShowShow, nsContatosDefault } from "./components";
import { NsContatosDefaultController } from "./default.form";
import { NsContatos } from "./factory";
import { NsContatosFormModalController, NsContatosFormService } from "./modal";
import { NsContatosFormModalShowController, NsContatosFormShowService } from "./modal.show";
import { NsContatosShowShowController } from "./show.form.show";

export const ContatosModule = angular
    .module('ContatosModule', [])
    .controller('NsContatosDefaultController', NsContatosDefaultController)
    .service('NsContatos', NsContatos)
    .controller('NsContatosFormModalShowController', NsContatosFormModalShowController)
    .controller('NsContatosFormModalController', NsContatosFormModalController)
    .service('NsContatosFormService', NsContatosFormService)
    .service('NsContatosFormShowService', NsContatosFormShowService)
    .controller('NsContatosShowShowController', NsContatosShowShowController)
    .component('nsContatosShowShow', nsContatosShowShow)
    .component('nsContatosDefault', nsContatosDefault)
    .name