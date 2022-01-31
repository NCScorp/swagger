import angular = require('angular');
import { TemplateOrdemServicoFormView } from '../interfaces/template-os.form';
import { TemplatesOrdemServicoItemChecklistService } from '../itens/checklist/templates-os-item-checklist.service';
import { TemplateOrdemServicoItemFormView } from '../itens/interfaces/template-os-item.form';
import { TemplatesOrdemServicoItemModalControllerRetorno } from '../itens/modal/templates-os-item-modal.controller';
import { TemplatesOrdemServicoItemModalService } from '../itens/modal/templates-os-item-modal.service';
import { TemplatesOrdemServicoItemService } from '../itens/templates-os-item.service';
import { TemplateOrdemServicoMaterialFormView } from '../materiais/interfaces/template-os-material.form';
import { TemplatesOrdemServicoMaterialModalService } from '../materiais/modal/templates-os-material-modal.service';
import { TemplatesOrdemServicoMaterialService } from '../materiais/templates-os-materiais.service';
import { TemplatesOrdemServicoService } from '../templates-os.service';

export class ListagemServicosFormController {

  static $inject = [
    '$state',
    '$scope',
    'toaster',
    'templatesOrdemServicoService',
    'utilService',
    'templatesOrdemServicoItemService',
    'templatesOrdemServicoMaterialService',
    'templatesOrdemServicoItemModalService',
    'templatesOrdemServicoMaterialModalService',
    'templatesOrdemServicoItemChecklistService'
  ];

  public entity: TemplateOrdemServicoFormView;
  public form: any;
  public constructors: any;
  public collection: any;
  public busy: boolean;
  public action: string;
  public idCount: number = 0;

  public busyServicos = false;

  constructor(
    public $state,
    public $scope: angular.IScope,
    public toaster: any,
    public templatesOrdemServicoService: TemplatesOrdemServicoService,
    public utilService: any,
    public templatesOrdemServicoItemService: TemplatesOrdemServicoItemService,
    public templatesOrdemServicoMaterialService: TemplatesOrdemServicoMaterialService,
    public templatesOrdemServicoItemModalService: TemplatesOrdemServicoItemModalService,
    public templatesOrdemServicoMaterialModalService: TemplatesOrdemServicoMaterialModalService,
    public templatesOrdemServicoItemChecklistService: TemplatesOrdemServicoItemChecklistService
  ) { }

  $onInit() {
    if (!this.$state.current.name.includes('_new')) {
      this.promiseCarregarServicos().then(() => this.reloadScope());
    } else {
      this.busyServicos = false;
      this.entity.servicosCarregado = true;
    }
    this.onSubmitSuccess();
    this.onSubmitError();
    this.onDeleteSucccess();
    this.onDeleteError();
  }

  /**
   * Promessa de carregamento dos serviços
   * @returns 
   */
  promiseCarregarServicos(): Promise<any> {
    return new Promise((resolve, reject) => {
      // Ativo busy de serviços
      this.busyServicos = true;

      // Realizo requisição de busca de serviços do template
      this.templatesOrdemServicoItemService.getAll({}, { ordemservicotemplate: this.entity.ordemservicotemplate }).then((arrItensApi) => {
        // Realizo conversão dos dados
        this.entity.arrItens = arrItensApi.map((item) => {
          return new TemplateOrdemServicoItemFormView(item);
        });

        // Defino que a busca por serviços já foi realizada
        this.entity.servicosCarregado = true;
        this.busyServicos = false;

        resolve(arrItensApi);
      }).catch((error) => {
        this.busyServicos = false;
        reject(error);
      })
    });
  }

  /**
   * Evento disparado ao clicar no botão de adicionar serviço
   */
  onBtnAddServicoClick() {
    let modal = this.templatesOrdemServicoItemModalService.open({
      ordemservicotemplate: this.entity.ordemservicotemplate
    }, null);

    modal.result.then((res: TemplatesOrdemServicoItemModalControllerRetorno) => {
      // Verifico se já existe um item com esse serviço técnico
      const possuiServicoTec = this.entity.arrItens.some((item) => {
        return item.servicotecnico.servicotecnico == res.entity.servicotecnico.servicotecnico;
      })

      if (possuiServicoTec) {
        this.toaster.pop({
          type: 'error',
          title: 'Já existe um serviço com este serviço técnico.'
        });
        return;
      }

      // Defino id virtual
      let maiorId = 0;
      this.entity.arrItens.forEach((item) => {
        if (item.idVirtual != null && item.idVirtual > maiorId) {
          maiorId = item.idVirtual;
        }
      });
      res.entity.idVirtual = ++maiorId;
      this.templatesOrdemServicoItemService.constructors = { ordemservicotemplate: this.entity.ordemservicotemplate };
      this.templatesOrdemServicoItemService.save(res.entity);
      this.busyServicos = true;
      
      // Aguardo a requisição completar para fazer a alteração visual no front
      this.$scope.$on('ordensservicos_templates_itens_submitted', (event, args) => {

        this.busyServicos = false;
        if (args?.response?.config?.method === 'POST') {
          
          // Verifico se possui checklist
          if (args.entity.arrChecklist.length) {
            this.templatesOrdemServicoItemChecklistService.constructors.ordemservicotemplateitem = args.entity.ordemservicotemplateitem;

            // Faço requisição para os checklists desse item
            args.entity.arrChecklist.forEach((checklistItem) => {

              if(checklistItem.ordemservicotemplateitem == null) checklistItem.ordemservicotemplateitem = args.entity.ordemservicotemplateitem; 
              this.templatesOrdemServicoItemChecklistService.save(checklistItem);
            });
          }

          
          if (this.entity.arrItens.length > 0){
            const itemIndex = this.entity.arrItens.findIndex((item) => item.ordemservicotemplateitem === args.entity.ordemservicotemplateitem);
            
            if (itemIndex !== -1) {
              // Atualizo o serviço da lista
              this.entity.arrItens.splice(itemIndex, 1, args.entity);
            } else {
              // Adiciono serviço a lista
              this.entity.arrItens.push(args.entity);
            }
            
          }
          else{
            // Adiciono serviço a lista
            this.entity.arrItens.push(args.entity);
          }

        }
      });
    }).catch((err) => { });
  }

  /**
   * Evento disparado ao clicar no botão de editar serviço
   */
  onBtnEdtServicoClick(servico: TemplateOrdemServicoItemFormView) {
    let modal = this.templatesOrdemServicoItemModalService.open({}, servico.clone());

    modal.result
      .then((res: TemplatesOrdemServicoItemModalControllerRetorno) => {
        // Verifico se já existe um item com esse serviço técnico
        const possuiServicoTec = this.entity.arrItens.some((item) => {
          return item.servicotecnico.servicotecnico == res.entity.servicotecnico.servicotecnico
            && (
              item.ordemservicotemplateitem != servico.ordemservicotemplateitem
              || item.idVirtual != servico.idVirtual
            );
        })

        if (possuiServicoTec) {
          this.toaster.pop({
            type: 'error',
            title: 'Já existe um serviço com este serviço técnico.'
          });
          return;
        }

        this.busyServicos = true;
        this.templatesOrdemServicoItemService.constructors = { ordemservicotemplate: this.entity.ordemservicotemplate };
        this.templatesOrdemServicoItemService.save(res.entity);

        // Aguardo a requisição completar para fazer a alteração visual no front
        this.$scope.$on('ordensservicos_templates_itens_submitted', (event, args) => {
          this.busyServicos = false;
          if (args?.response?.config?.method === 'PUT') {
            // Atualizo entidade
            servico.checklistCarregado = res.entity.checklistCarregado;
            servico.arrChecklist = res.entity.arrChecklist;
            servico.arrChecklistExcluir = res.entity.arrChecklistExcluir;
            servico.servicotecnico = res.entity.servicotecnico;
            servico.quantidade = res.entity.quantidade;
            servico.valor = res.entity.valor;

            this.templatesOrdemServicoItemChecklistService.constructors.ordemservicotemplateitem = args.entity.ordemservicotemplateitem;

            if (res.entity.arrChecklist.length) {
            // Faço requisição para os checklists desse item
              res.entity.arrChecklist.forEach((checklistItem) => {
                checklistItem.ordemservicotemplateitem = args.entity.ordemservicotemplateitem;
                checklistItem.ordemservicotemplate = this.entity.ordemservicotemplate;

                this.templatesOrdemServicoItemChecklistService.save(checklistItem);
              });
            }
            if (res.entity.arrChecklistExcluir.length) {
              res.entity.arrChecklistExcluir.forEach((checklistItem) => {
                checklistItem.ordemservicotemplateitem = args.entity.ordemservicotemplateitem;
                this.templatesOrdemServicoItemChecklistService.delete(checklistItem.ordemservicotemplatechecklist, true);
              });
            }
          }
        });

      })
      .catch((err) => { });
  }

  /**
   * Evento disparado ao clicar no botão de excluir serviço
   * @param checklist 
  */
  onBtnExcluirServicoClick(servico: TemplateOrdemServicoItemFormView) {
    let indexExclusao = -1;

    // Adiciono a lista de serviços para exclusão
    this.entity.arrItensExcluir.push(servico);
    this.templatesOrdemServicoItemService.excluir(servico, { ordemservicotemplate: this.entity.ordemservicotemplate });
    this.busyServicos = true;

    // Aguardo a requisição completar para fazer a alteração visual no front
    this.$scope.$on('ordensservicos_templates_itens_deleted', (event, args) => {

      indexExclusao = this.entity.arrItens.findIndex((item) => item.ordemservicotemplateitem == args.entity.ordemservicotemplateitem);

      this.busyServicos = false;

      // Removo da lista principal
      if(indexExclusao > -1) this.entity.arrItens.splice(indexExclusao, 1);
      this.entity.arrItensExcluir = [];

    });

    this.reloadScope();
  }

  /**
   * Verifica se os dados gerais do template de OS foram modificados desde o último salvamento
   * @returns 
   */
  templateFoiModificado(): boolean {
    return true;
  }

  /**
   * Verifica se o serviço foi modificado desde o último salvamento
   * @param servico 
   * @returns 
   */
  servicoFoiModificado(servico: TemplateOrdemServicoItemFormView): boolean {
    return true;
  }

  /**
   * Atualiza escopo da tela
  */
  reloadScope() {
    this.$scope.$applyAsync();
  }

  /**
   * Evento observado ao fazer um submit com sucesso
   */
  onSubmitSuccess(): void {
    this.$scope.$on('ordensservicos_templates_itens_submitted', (event, args) => {
      this.busyServicos = false;
      if (args?.response?.config?.method === 'POST') {
        this.toaster.pop({
          type: 'success',
          title: 'Serviço inserido com sucesso!'
        });
      } else {
        this.toaster.pop({
          type: 'success',
          title: 'Serviço editado com sucesso!'
        });
      }
    });

  }

  /**
   * Evento observado ao ter um erro durante um submit
   */
  onSubmitError(): void {
    this.$scope.$on('ordensservicos_templates_itens_submit_error', (event, args) => {
      this.busyServicos = false;

      this.toaster.pop({
        type: 'error',
        title: 'Ocorreu um erro ao enviar o serviço.'
      });
    });
  }

  /**
   * Evento observado ao fazer um delete com sucesso
   */
  onDeleteSucccess(): void {
    this.$scope.$on('ordensservicos_templates_itens_deleted', () => {
      this.busyServicos = false;

      this.toaster.pop({
        type: 'success',
        title: 'Serviço excluído com sucesso!'
      });
    });
  }

  /**
   * Evento observado ao ter um erro durante um delete
   */
  onDeleteError(): void {
    this.$scope.$on('ordensservicos_templates_itens_delete_error', () => {
      this.busyServicos = false;

      this.toaster.pop({
        type: 'error',
        title: 'Ocorreu um erro ao excluir o serviço.'
      });
    });
  }
}