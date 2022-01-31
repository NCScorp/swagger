
import * as angular from 'angular';
import { FullscreenService } from './fullscreen.service';

export const commonsModule =
    angular.module('commons', [])     
    .service('FullscreenService', FullscreenService)
    .name;



