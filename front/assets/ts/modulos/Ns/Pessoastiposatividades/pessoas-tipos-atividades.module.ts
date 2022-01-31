import angular from "angular";
import { nsPessoastiposatividadesDefaultShow, nsPessoastiposatividadesDefault } from "./components";
import { NsPessoastiposatividadesDefaultController } from "./default.form";
import { NsPessoastiposatividadesDefaultShowController } from "./default.form.show";
import { NsPessoastiposatividades } from "./factory";

export const PessoastiposatividadesModule =  angular
.module('PessoastiposatividadesModule',[])
.controller('NsPessoastiposatividadesDefaultController', NsPessoastiposatividadesDefaultController)
.service('NsPessoastiposatividades', NsPessoastiposatividades)
.component('nsPessoastiposatividadesDefaultShow',nsPessoastiposatividadesDefaultShow)
.component('nsPessoastiposatividadesDefault',nsPessoastiposatividadesDefault)
.controller('NsPessoastiposatividadesDefaultShowController', NsPessoastiposatividadesDefaultShowController)
.name