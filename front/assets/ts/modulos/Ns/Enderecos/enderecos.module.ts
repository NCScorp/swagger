import angular from "angular";
import { nsEnderecosShowShow, nsEnderecosDefault } from "./components";
import { NsEnderecosDefaultController } from "./default.form";
import { NsEnderecos } from "./factory";
import { NsEnderecosFormModalController, NsEnderecosFormService } from "./modal";
import { NsEnderecosFormModalShowController, NsEnderecosFormShowService } from "./modal.show";
import { NsEnderecosShowShowController } from "./show.form.show";

export const EnderecosModule = angular
.module('EnderecosModule',[])
.controller('NsEnderecosDefaultController', NsEnderecosDefaultController)
.service('NsEnderecos', NsEnderecos)
.controller('NsEnderecosFormModalShowController', NsEnderecosFormModalShowController)
.controller('NsEnderecosFormModalController', NsEnderecosFormModalController)
.controller('NsEnderecosShowShowController', NsEnderecosShowShowController)
.service('NsEnderecosFormService', NsEnderecosFormService)
.service('NsEnderecosFormShowService', NsEnderecosFormShowService)
.component('nsEnderecosShowShow',nsEnderecosShowShow)
.component('nsEnderecosDefault',nsEnderecosDefault)
.name