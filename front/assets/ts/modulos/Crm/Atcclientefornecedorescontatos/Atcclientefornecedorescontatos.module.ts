import angular from "angular";
import { CrmAtcclientefornecedorescontatosListController } from "./index";
import { AtcClienteFornecedoresContatosRoutes } from "./config";
import { CrmAtcclientefornecedorescontatos } from "./factory";

export const AtcClienteFornecedoresContatosModule = angular
    .module('Atcclientefornecedorescontatos', [])
    .controller('CrmAtcclientefornecedorescontatosListController',CrmAtcclientefornecedorescontatosListController)
    .service('CrmAtcclientefornecedorescontatos',CrmAtcclientefornecedorescontatos)
    .config(AtcClienteFornecedoresContatosRoutes)
    .constant('FIELDS_CrmAtcclientefornecedorescontatos', [
    ])
    .run(
        ['$rootScope', 'FIELDS_CrmAtcclientefornecedorescontatos',
            ($rootScope: any, FIELDS_CrmAtcclientefornecedorescontatos: object) => {
                $rootScope.FIELDS_CrmAtcclientefornecedorescontatos = FIELDS_CrmAtcclientefornecedorescontatos;
            }
        ]
    ).name