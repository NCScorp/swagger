import angular = require('angular');
import { CrmAtcsareas } from './modulos/Crm/Atcsareas/factory';
import { FullscreenService } from './modulos/Commons/fullscreen.service';

export class MenuController {

    static $inject = [
      '$http',
      'nsjRouting',
      '$scope',   
      '$window',
      'Usuarios',
      'CrmAtcsareas',
      'FullscreenService'
    ];

    public atcsAreas:any;
    public listGrupoEmpresarial: Array<any> = [];
    public selectedId: string;
    grupoEmpresarialHtml: string;

    constructor(
      public $http:angular.IHttpService,
      public nsjRouting: any,
      public $scope: angular.IScope,
      public $window: angular.IWindowService, 
      public Usuarios: any,
      public CrmAtcsareas: CrmAtcsareas,
      public fullscreenService: FullscreenService
    ) {}

    $onInit() {
        this.$scope.$on('getProfile', (event: any, entities: any) => {
          this.optionListGrupoEmpresarial(entities);
          this.grupoEmpresarialHtml = this.getHtmlGrupo(); 
        });
        this.$scope.$on('crm_atcsareas_list_finished' , (event: any,atcsAreas: any[])=>{
          this.atcsAreas = [...atcsAreas];
          this.$scope.$applyAsync();
        });
        
        this.$scope.$on('fullscreen_modificado' , (event: any, ativado: boolean)=>{
            this.reloadScope();
        });

        this.carregarAtcsAreas();
    }

    private async carregarAtcsAreas() {

      this.CrmAtcsareas.reload();

    }

    /**
   * Preenche o select de grupos empresariais do usuário
   * @param entities
   */
    private optionListGrupoEmpresarial(entities) {

      let tenant = this.$window.nsj.globals.a.tenant;
      let lstGrupos = entities.organizacoes.find(x => x.codigo === tenant).gruposempresariais;

      this.listGrupoEmpresarial = [ ];

      lstGrupos.forEach(item => {
        
        var obj = {
          codigo: item.codigo,
          descricao: item.descricao
        };

        this.listGrupoEmpresarial.push(obj);

      });

      this.selectedId = this.$window.nsj.globals.a.grupoempresarial;
    }

    /**
     * Atualiza o escopo da página
     */
    private reloadScope(){
        this.$scope.$applyAsync();
    }

    /**
     * Alterar o grupo empresarial utilizado no sistema
     */
    public changeGrupoEmpresarial = ({grupoempresarial})=> {
      let tenant = this.$window.nsj.globals.a.tenant;
      let grupo = grupoempresarial;
      let landingUrl = `/${tenant}/${grupo}/crm/dashboard-atendimentos-pendencias`;
      
      //Redireciono para nova url com grupoempresarial atualizado
      this.$window.location.href = landingUrl;
    }

    getHtmlGrupo(){
      return `
      <div style="padding:10px;">
                        <label>Grupo Empresarial</label>
                        <select class="form-control" ng-change="menuCtrl.changeGrupoEmpresarial()"
                            ng-options="item.codigo as item.descricao for item in menuCtrl.listGrupoEmpresarial"
                            ng-model="menuCtrl.selectedId" >
                        </select>
                    </div>
      `
    }

    temPermissao(chave:any){      
      return this.Usuarios.temPermissao(chave);
    }
}
