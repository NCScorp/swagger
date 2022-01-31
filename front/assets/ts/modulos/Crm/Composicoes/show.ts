import angular = require('angular');

export class CrmComposicoesFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmComposicoes', 'toaster'
    , 'entity'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.constructors = entityService.constructors;
    }}
