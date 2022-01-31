import angular = require('angular');
import { TemplateOrdemServicoFormView } from '../interfaces/template-os.form';
import { TemplatesOrdemServicoItemModalService } from '../itens/modal/templates-os-item-modal.service';
import { TemplatesOrdemServicoItemService } from '../itens/templates-os-item.service';
import { TemplateOrdemServicoMaterialFormView } from '../materiais/interfaces/template-os-material.form';
import { TemplatesOrdemServicoMaterialModalControllerRetorno } from '../materiais/modal/templates-os-material-modal.controller';
import { TemplatesOrdemServicoMaterialModalService } from '../materiais/modal/templates-os-material-modal.service';
import { TemplatesOrdemServicoMaterialService } from '../materiais/templates-os-materiais.service';
import { TemplatesOrdemServicoService } from '../templates-os.service';

export class ListagemMateriaisFormController {

  static $inject = [
    '$state',
    '$scope',
    'toaster',
    'utilService',
    'templatesOrdemServicoItemService',
    'templatesOrdemServicoMaterialService',
    'templatesOrdemServicoItemModalService',
    'templatesOrdemServicoMaterialModalService',
    'templatesOrdemServicoService'
  ];

  public entity: TemplateOrdemServicoFormView;
  public form: any;
  public constructors: any;
  public collection: any;
  public busy: boolean;
  public action: string;
  public idCount: number = 0;
  public busyMateriais = false;

  constructor(
    public $state,
    public $scope: angular.IScope,
    public toaster: any,
    public utilService: any,
    public templatesOrdemServicoItemService: TemplatesOrdemServicoItemService,
    public templatesOrdemServicoMaterialService: TemplatesOrdemServicoMaterialService,
    public templatesOrdemServicoItemModalService: TemplatesOrdemServicoItemModalService,
    public templatesOrdemServicoMaterialModalService: TemplatesOrdemServicoMaterialModalService,
    public templatesOrdemServicoService: TemplatesOrdemServicoService
  ) {
   }

  $onInit() {
    if (!this.$state.current.name.includes('_new')) {
      this.promiseCarregarMateriais().then(() => this.reloadScope());
    } else {
      this.entity.materiaisCarregado = true;
      this.busyMateriais = false;
    }
    this.onSubmitSuccess();
    this.onSubmitError();
    this.onDeleteSucccess();
    this.onDeleteError();
  }

  /**
   * Promessa de carregamento dos materiais
   * @returns 
   */
  promiseCarregarMateriais(): Promise<any> {
    return new Promise((resolve, reject) => {
      // Ativo busy de serviços
      this.busyMateriais = true;

      // Realizo requisição de busca de materiais do template
      this.templatesOrdemServicoMaterialService.getAll({}, { ordemservicotemplate: this.entity.ordemservicotemplate }).then((arrMateriaisApi) => {
        // Realizo conversão dos dados
        this.entity.arrMateriais = arrMateriaisApi.map((material) => {
          return new TemplateOrdemServicoMaterialFormView(material);
        })
        // Defino que a busca por materiais já foi realizada
        this.entity.materiaisCarregado = true;
        this.busyMateriais = false;
        resolve(arrMateriaisApi);
      }).catch((error) => {
        this.busyMateriais = false;
        reject(error);
      })
    });
  }

  /**
   * Evento disparado ao clicar no botão de adicionar material
   */
  onBtnAddMaterialClick(): void {
    let modal = this.templatesOrdemServicoMaterialModalService.open({
      ordemservicotemplate: this.entity.ordemservicotemplate
    }, null);

    modal.result.then((res: TemplatesOrdemServicoMaterialModalControllerRetorno) => {
      this.busyMateriais = true;
      // Defino id virtual
      let maiorId = 0;
      this.entity.arrMateriais.forEach((item) => {
        if (item.idVirtual != null && item.idVirtual > maiorId) {
          maiorId = item.idVirtual;
        }
      });
      res.entity.idVirtual = ++maiorId;

      // Adiciono material a lista
      this.entity.arrMateriais.push(res.entity);
      this.templatesOrdemServicoMaterialService.constructors = { ordemservicotemplate: this.entity.ordemservicotemplate };
      this.templatesOrdemServicoMaterialService.save(res.entity);
    }).catch((err) => { });
  }

  /**
   * Evento disparado ao clicar no botão de editar material
   */
  onBtnEdtMaterialClick(material: TemplateOrdemServicoMaterialFormView): void {
    let modal = this.templatesOrdemServicoMaterialModalService.open({}, material.clone());

    modal.result
      .then((res: TemplatesOrdemServicoMaterialModalControllerRetorno) => {
        // Atualizo entidade
        this.busyMateriais = true;
        material.item = res.entity.item;
        material.quantidade = res.entity.quantidade;
        this.templatesOrdemServicoMaterialService.constructors = { ordemservicotemplate: this.entity.ordemservicotemplate };
        this.templatesOrdemServicoMaterialService.save(material);
      })
      .catch((err) => { })
  }

  /**
   * Evento disparado ao clicar no botão de excluir material
   * @param checklist 
   */
  onBtnExcluirMaterialClick(material: TemplateOrdemServicoMaterialFormView): void {
    const existeNoBD = material.ordemservicotemplatematerial != null;

    let indexExclusao = -1;

    // Se existe no banco de dados
    if (existeNoBD) {
      this.busyMateriais = true;
      this.templatesOrdemServicoMaterialService.excluir(material, { ordemservicotemplate: this.entity.ordemservicotemplate });

      indexExclusao = this.entity.arrMateriais.findIndex((item) => item.ordemservicotemplatematerial == material.ordemservicotemplatematerial);
    } else {
      indexExclusao = this.entity.arrMateriais.findIndex((item) => item.idVirtual == material.idVirtual);
    }

    // Removo da lista principal
    this.entity.arrMateriais.splice(indexExclusao, 1);
  }


  /**
   * Verifica se os dados gerais do template de OS foram modificados desde o último salvamento
   * @returns
   */
  templateFoiModificado(): boolean {
    return true;
  }

  /**
   * Verifica se o material foi modificado desde o último salvamento
   * @param material 
   * @returns 
   */
  materialFoiModificado(material: TemplateOrdemServicoMaterialFormView): boolean {
    return true;
  }

  /**
   * Atualiza escopo da tela
  */
  reloadScope(): void {
    this.$scope.$applyAsync();
  }

  /**
   * Evento observado ao fazer um submit com sucesso
   */
  onSubmitSuccess(): void {
    this.$scope.$on('ordensservicos_templates_materiais_submitted', (event, args) => {
      this.busyMateriais = false;
      if (args?.response?.config?.method === 'POST') {
        this.toaster.pop({
          type: 'success',
          title: 'Material inserido com sucesso!'
        });
      } else {
        this.toaster.pop({
          type: 'success',
          title: 'Material editado com sucesso!'
        });
      }
    });

  }

  /**
   * Evento observado ao ter um erro durante um submit
   */
  onSubmitError(): void {
    this.$scope.$on('ordensservicos_templates_materiais_submit_error', (event, args) => {
      this.busyMateriais = false;

      this.toaster.pop({
        type: 'error',
        title: 'Ocorreu um erro ao enviar o material.'
      });
    });
  }

  /**
   * Evento observado ao fazer um delete com sucesso
   */
  onDeleteSucccess(): void {
    this.$scope.$on('ordensservicos_templates_materiais_deleted', () => {
      this.busyMateriais = false;

      this.toaster.pop({
        type: 'success',
        title: 'Material excluído com sucesso!'
      });
    });
  }

  /**
   * Evento observado ao ter um erro durante um delete
   */
  onDeleteError(): void {
    this.$scope.$on('ordensservicos_templates_materiais_delete_error', () => {
      this.busyMateriais = false;

      this.toaster.pop({
        type: 'error',
        title: 'Ocorreu um erro ao excluir o material.'
      });
    });
  }
}