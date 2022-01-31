import angular from "angular";
import { nsTelefonesShowShow, nsTelefonesDefault } from "./components";
import { NsTelefonesDefaultController } from "./default.form";
import { NsTelefones } from "./factory";
import { NsTelefonesFormModalController, NsTelefonesFormService } from "./modal";
import { NsTelefonesFormModalShowController, NsTelefonesFormShowService } from "./modal.show";
import { NsTelefonesShowShowController } from "./show.form.show";

export const TelefonesModule = angular
.module('TelefonesModule',[])
.controller('NsTelefonesDefaultController', NsTelefonesDefaultController)
.service('NsTelefones', NsTelefones)
.controller('NsTelefonesFormModalShowController', NsTelefonesFormModalShowController)
.controller('NsTelefonesFormModalController', NsTelefonesFormModalController)
.controller('NsTelefonesShowShowController', NsTelefonesShowShowController)
.service('NsTelefonesFormService', NsTelefonesFormService)
.service('NsTelefonesFormShowService', NsTelefonesFormShowService)
.component('nsTelefonesShowShow',nsTelefonesShowShow)
.component('nsTelefonesDefault',nsTelefonesDefault)
.name