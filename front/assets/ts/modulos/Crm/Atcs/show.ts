import angular = require('angular');

export class CrmAtcsFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcs', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negocio') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
        this.constructors = entityService.constructors;
    }}

