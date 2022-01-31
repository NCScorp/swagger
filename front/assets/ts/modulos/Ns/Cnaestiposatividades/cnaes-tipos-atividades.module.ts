import angular from "angular";
import { nsCnaestiposatividadesDefaultShow, nsCnaestiposatividadesDefault } from "./components";
import { NsCnaestiposatividadesDefaultController } from "./default.form";
import { NsCnaestiposatividadesDefaultShowController } from "./default.form.show";
import { NsCnaestiposatividades } from "./factory";

export const CnaestiposatividadesModule = angular
    .module('CnaestiposatividadesModule', [])
    .controller('NsCnaestiposatividadesDefaultShowController', NsCnaestiposatividadesDefaultShowController)
    .controller('NsCnaestiposatividadesDefaultController', NsCnaestiposatividadesDefaultController)
    .service('NsCnaestiposatividades', NsCnaestiposatividades)
    .component('nsCnaestiposatividadesDefaultShow', nsCnaestiposatividadesDefaultShow)
    .component('nsCnaestiposatividadesDefault', nsCnaestiposatividadesDefault)
    .name