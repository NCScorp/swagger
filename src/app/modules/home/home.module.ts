import * as angular from 'angular';
import { HomeController } from './home.controller';
import { HomeRouting } from './home.routes';

import { HomeService } from './home.service';

export const HomeModule = angular.module('homeModule', ['ui.router.state'])
    .service('homeService', HomeService)
    .controller('homeController', HomeController)
    .config(HomeRouting)
    .name;