import angular from "angular";
import { CrmHistoricoAtcFormModalController, CrmAtcsFormHistoricoService } from "./modal";

export const AtcshistoricoModule = angular
    .module('AtcshistoricoModule', [])
    .controller('CrmHistoricoAtcFormModalController', CrmHistoricoAtcFormModalController)
    .service('CrmAtcsFormHistoricoService', CrmAtcsFormHistoricoService)
    .name