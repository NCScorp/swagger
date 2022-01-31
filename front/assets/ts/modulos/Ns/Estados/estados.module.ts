import angular from "angular";
import { nsEstadosDefaultShow } from "./components";
import { NsEstadosDefaultShowController } from "./default.form.show";
import { NsEstados } from "./factory";

export const EstadosModule = angular
    .module('EstadosModule', [])
    .controller('NsEstadosDefaultShowController', NsEstadosDefaultShowController)
    .service('NsEstados', NsEstados)
    .component('nsEstadosDefaultShow', nsEstadosDefaultShow)
    .name