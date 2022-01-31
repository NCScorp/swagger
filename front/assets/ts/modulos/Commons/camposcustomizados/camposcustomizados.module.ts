import angular from "angular";
import { CamposcustomizadosService } from "./CamposcustomizadosService";

export const camposCustomizadosModule = angular
    .module('camposCustomizadosModule', [])
    .service('CamposcustomizadosService', CamposcustomizadosService)
    .name