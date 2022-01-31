import angular from "angular";
import { GooglePlacesService } from "./places.factory";

export const GooglePlacesModule = angular
    .module('GooglePlacesModule', [])
    .service('GooglePlacesService', GooglePlacesService)
    .name