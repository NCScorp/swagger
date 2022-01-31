import angular from "angular";
import { CrmAtcsRelacionadosController } from "./index";

export const AtcsrelacionadosModule = angular
    .module('AtcsrelacionadosModule', [])
    .controller('CrmAtcsRelacionadosController', CrmAtcsRelacionadosController)
    .name;

