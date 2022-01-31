import angular from "angular";
import ngInject from "./ngInject";
import { CommonsUtilsService } from "./utils.service";

export const UtilsModule = angular
    .module('utilsModule', [])
    .directive('ngInject',ngInject)
    .service('CommonsUtilsService', CommonsUtilsService)
    .name