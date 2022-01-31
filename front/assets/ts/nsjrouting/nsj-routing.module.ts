import angular = require('angular');

import { NsjRoutingService } from "./nsj-routing.service";

export const nsjRoutingModule = angular.module('nsjRouting', [])
    .service('nsjRouting', NsjRoutingService).name;
