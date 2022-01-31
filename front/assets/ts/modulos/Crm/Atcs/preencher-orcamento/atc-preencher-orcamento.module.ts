import angular from "angular";
import { atcpreencherorcamentoRoutes } from "./config";
import { CrmAtcsPreencherOrcamentoFornecedorController } from "./controller";



export const atcpreencherorcamentoModule = angular.module('atcpreencherorcamentoModule', [])
    .controller('CrmAtcsPreencherOrcamentoFornecedorController', CrmAtcsPreencherOrcamentoFornecedorController)
    .config(atcpreencherorcamentoRoutes)
    .name