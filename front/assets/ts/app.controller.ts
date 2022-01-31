import angular = require('angular');
import { CrmAtcsareas } from './modulos/Crm/Atcsareas/factory';
import menuJSON from '../../config/menu.json';
import { FullscreenService } from './modulos/Commons/fullscreen.service';
import { NsjAuth } from '@nsj/core';
import { AuthServiceTipo } from '@nsj/core/authentication/auth-service-tipo.enum';

export class AppController {

  static $inject = [
    '$http',
    'nsjRouting',
    '$scope',
    '$window',
    'Usuarios',
    'CrmAtcsareas',
    'FullscreenService'
  ];

  public atcsAreas: any = [];
  public listGrupoEmpresarial: Array<any> = [];
  public selectedId: string;
  public grupoEmpresarialHtml: string;
  public menuList = menuJSON
  public menuEscondido: boolean = false;

  constructor(
    public $http: angular.IHttpService,
    public nsjRouting: any,
    public $scope: angular.IScope,
    public $window: angular.IWindowService,
    public Usuarios: any,
    public CrmAtcsareas: CrmAtcsareas,
    public fullscreenService: FullscreenService
  ) {

  }

  $onInit() {

    this.$scope.$on('crm_atcsareas_list_finished', (event: any, atcsAreas: any[]) => {
        this.atcsAreas = [...atcsAreas];
        this.reloadScope();
    })

    this.$scope.$on('fullscreen_modificado' , (event: any, ativado: boolean)=>{
        this.reloadScope();
    });

    if (this.temPermissao('crm_atcsareas_index')){
        this.carregarAtcsAreas();
    }

    if (NsjAuth.tipoAuth == AuthServiceTipo.astHash) {
        this.menuEscondido = true;
    }
  }

  private async carregarAtcsAreas() {

    this.CrmAtcsareas.reload();

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
  public changeGrupoEmpresarial = ({ grupoempresarial }) => {

    let tenant = this.$window.nsj.globals.a.tenant;
    let grupo = grupoempresarial;

    let landingUrl = `/${tenant}/${grupo}/crm/dashboard-atendimentos-pendencias`;

    //Redireciono para nova url com grupoempresarial atualizado
    this.$window.location.href = landingUrl;
  }


  temPermissao(chave: any) {
    return this.Usuarios.temPermissao(chave);
  }

}

function getUniqueListBy<Type>(arr: Type[], key: string) {
  const arrMap = arr.map((item: Required<Type>) => [item[key], item]);
  return Array.from(new Map<any,Type>(arrMap as any).values())
}