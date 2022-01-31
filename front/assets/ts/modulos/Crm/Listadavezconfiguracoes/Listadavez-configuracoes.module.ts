import angular from "angular";
import { CrmListadavezconfiguracoes } from "./factory";

export const ListadavezconfiguracoesModule = angular
    .module('ListadavezconfiguracoesModule', [])
    .service('CrmListadavezconfiguracoes', CrmListadavezconfiguracoes)
    .name;