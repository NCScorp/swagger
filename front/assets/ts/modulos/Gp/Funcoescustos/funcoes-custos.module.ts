import angular from "angular";
import { gpFuncoescustosDefault } from "./components";
import { GpFuncoescustosDefaultController } from "./default.form";
import { GpFuncoescustos } from "./factory";
import { GpFuncoescustosFormModalController, GpFuncoescustosFormService } from "./modal";

export const FuncoescustosModule = angular
    .module('FuncoescustosModule', [])
    .component('gpFuncoescustosDefault', gpFuncoescustosDefault)
    .controller('GpFuncoescustosDefaultController', GpFuncoescustosDefaultController)
    .service('GpFuncoescustos', GpFuncoescustos)
    .controller('GpFuncoescustosFormModalController', GpFuncoescustosFormModalController)
    .service('GpFuncoescustosFormService', GpFuncoescustosFormService)
    .name
