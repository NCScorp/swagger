declare var nsj;
import angular = require('angular');
import {NsjConfig} from './core/nsj-core';
import {NsjStorage} from "./utils/storage";

export class AppController {

    static $inject = [
        'Usuario',
        '$window',
        '$scope'
    ];

    public listGrupoEmpresarial: Array<any> = [];
    public selectedId: string;

    constructor(
        public usuario: any,
        public $window: angular.IWindowService,
        public $scope: any
    ) {

        //TODO: para manter compatibilidade com componetes Nasajon-ui
        this.setGlobals(usuario.profile);

    }

    $onDestroy(){
       NsjStorage.deleteAll();
    }

    private setGlobals(profile) {

        nsj.globals.getInstance().setGlobals({
            tenant: NsjConfig.config.globals.tenantCodigo,
            grupoempresarial: NsjConfig.config.globals.grupoEmpresarialCodigo,
            links: profile.links
        });
    }

    /**
     * Alterar o grupo empresarial utilizado no sistema
     */
    public changeGrupoEmpresarial = (grupoempresarial) => {
        let tenant = this.$window.nsj.globals.a.tenant;
        let grupo = grupoempresarial.grupoempresarial;
        let landingUrl = `/${tenant}/${grupo}/paginainicial`;

        //Redireciono para nova url com grupoempresarial atualizado
        this.$window.location.href = landingUrl;
    }

}
