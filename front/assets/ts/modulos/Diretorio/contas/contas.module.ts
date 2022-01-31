import angular from "angular";
import { DiretorioContas } from "./contas.service";

export const DiretorioContasModule = angular
    .module('DiretorioContasModule', [])
    .service('DiretorioContas', DiretorioContas)
    .name;
