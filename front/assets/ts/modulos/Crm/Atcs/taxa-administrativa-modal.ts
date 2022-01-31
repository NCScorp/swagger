import angular = require('angular');
import { timeStamp } from 'console';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';
import { ModalGerarFaturamentoService } from './gerar-faturamento-modal';

export class TaxaAdministrativaModalController {
  static $inject = [
    'toaster',
    '$uibModalInstance',
    'entity',
    'constructors',
    '$http',
    'nsjRouting',
    'CommonsUtilsService',
    '$scope',
    'ModalGerarFaturamentoService',
    '$rootScope',
  ];

  public action: string;
  public form: any;
  public submitted: boolean = false;

  //Dados da tabela
  public estabelecimento: any;
  public seguradora: any;
  public servico: any;
  public formapagamento: any;
  public municipioprestacao: any;
  public valor: any;
  public modoVisualizacao: boolean; //Indica se o usuário pode ou não editar dados da taxa administrativa
  public idSeguradora: any; //Usado na geração do faturamento
  public busy: boolean = false;

  constructor(
    public toaster: any,
    public $uibModalInstance: any,
    public entity: any,
    public constructors: any,
    public $http: angular.IHttpService,
    public nsjRouting: any,
    public CommonsUtilsService: CommonsUtilsService,
    public $scope: any,
    public ModalGerarFaturamentoService: ModalGerarFaturamentoService,
    public $rootScope: any
  ) {}

  // Iniciar com os dados da tabela preenchidos
  async $onInit() {
    // Se o contrato já foi gerado, os dados dela não são editáveis e eles serão exibidos na tabela
    if (this.entity.contratotaxaadm != null) {
      this.modoVisualizacao = true;
      this.formapagamento = this.entity.formapagamentotaxaadm;
      this.municipioprestacao = this.entity.municipioprestacaotaxaadm;
      this.valor = this.entity.valortaxaadm;
    }

    // Se a taxa foi preenchida, uso os dados da configuração nesses campos e os dados são editáveis
    else if (this.entity.valortaxaadm) {
      this.modoVisualizacao = false;
      this.formapagamento = this.entity.formapagamentotaxaadm;
      this.municipioprestacao = this.entity.municipioprestacaotaxaadm;
      this.valor = this.entity.valortaxaadm;
    }

    // Não tem valor no atc
    else {
      this.modoVisualizacao = false;
      this.formapagamento = this.entity.listaConfigTaxaAdm[0].formapagamento;
      this.municipioprestacao = this.entity.listaConfigTaxaAdm[0].municipioprestacao;
      this.valor = this.entity.listaConfigTaxaAdm[0].valor;
    }

    this.estabelecimento = this.entity.estabelecimento.nomefantasia;
    this.seguradora = this.entity.cliente.razaosocial;
    this.servico = this.entity.listaConfigTaxaAdm[0].itemfaturamento.descricaoservico;
    this.idSeguradora = this.entity.cliente.cliente;
  }

  valid() {
    return true;
  }

  submit() {
    this.submitted = true;
    if (this.valid()) {
      this.$uibModalInstance.close(this.entity);
    } else {
      this.toaster.pop({
        type: 'error',
        title: 'Erro!',
      });
    }
  }

  close(result?: any) {
    if (result) {
      return this.$uibModalInstance.close(result);
    }

    this.$uibModalInstance.dismiss('fechar');
  }

  // Gerar faturamento
  gerarFaturamento() {
    let dataToSend = {
      configuracaotaxaadm: this.entity.listaConfigTaxaAdm[0],
      formapagamentotaxaadm: this.formapagamento,
      municipioprestacaotaxaadm: this.municipioprestacao,
      pessoa: this.idSeguradora,
      valortaxaadm: this.valor,
      atc: this.entity.negocio,
    };

    dataToSend.municipioprestacaotaxaadm.pessoamunicipio = dataToSend.municipioprestacaotaxaadm.pessoamunicipio
      ? dataToSend.municipioprestacaotaxaadm.pessoamunicipio
      : dataToSend.municipioprestacaotaxaadm.municipioprestacao;

    delete dataToSend.municipioprestacaotaxaadm.municipioprestacao;

    this.abrirModalGerarFaturamento(dataToSend);

    // Se conseguiu gerar o faturamento, mudo a modal para o modo de visualização e obtenho os dados de criação do faturamento da taxa
    this.$scope.$on('crm_atcs_gera_contrato_taxa_administrativa_success', async (event: any) => {
      this.busy = true;

      // Consultando o atc para obter os campos createdcontratotaxa_at e createdcontratotaxa_by. Campos usados na mensagem que indica quando e por quem o faturamento foi feito
      let negocio: any = await this.$http({
        method: 'GET',
        url: this.nsjRouting.generate('crm_atcs_get', { id: this.entity.negocio }, true),
      });
      const { createdcontratotaxa_at, createdcontratotaxa_by, contratotaxaadm ,valortaxaadm} = negocio.data;

      this.entity.createdcontratotaxa_at = createdcontratotaxa_at;
      this.entity.createdcontratotaxa_by = createdcontratotaxa_by;

      this.$rootScope.$broadcast('crm_atcs_gera_contrato_taxa_administrativa_success_atualiza_atc', {
        contratotaxaadm,
        createdcontratotaxa_at,
        createdcontratotaxa_by,
        valortaxaadm
      });

      this.modoVisualizacao = true;
      this.busy = false;
    });
  }

  /**
   * Recebe uma data em formato de string e retorna data no formato 'DD/MM/YYYY [às] HH:mm'
   * @param dataStr
   */
  getDataFormatada(dataStr: string) {
    return this.CommonsUtilsService.getDataFormatada(dataStr, 'DD/MM/YYYY [às] HH:mm');
  }

  // Salvar escolha Atc
  salvarEscolhasAtc() {
    let dataToSend = {
      configuracaotaxaadm: this.entity.listaConfigTaxaAdm[0],
      formapagamentotaxaadm: this.formapagamento,
      municipioprestacaotaxaadm: this.municipioprestacao,
      pessoa: this.idSeguradora,
      valortaxaadm: this.valor,
    };

    dataToSend.municipioprestacaotaxaadm.pessoamunicipio = dataToSend.municipioprestacaotaxaadm.pessoamunicipio
      ? dataToSend.municipioprestacaotaxaadm.pessoamunicipio
      : dataToSend.municipioprestacaotaxaadm.municipioprestacao;

    delete dataToSend.municipioprestacaotaxaadm.municipioprestacao;

    this.$http({
      method: 'POST',
      data: dataToSend,
      url: this.nsjRouting.generate('crm_atcs_salva_contrato_taxa_administrativa', { id: this.entity.negocio }),
    })
      .then((result) => {
        this.toaster.pop({
          type: 'success',
          title: 'Sucesso ao salvar os dados da taxa!',
        });
        this.$rootScope.$broadcast('crm_atcs_salva_contrato_taxa_administrativa', { valortaxaadm: this.valor });
        this.close(result.data);
      })
      .catch((error) => {
        this.toaster.pop({
          type: 'error',
          title: error.data?.message != null ? error.data.message : error,
        });
        this.close();
      });
  }

  // Cancelar faturamento
  cancelarFaturamento() {
    let dataToSend = {
      configuracaotaxaadm: this.entity.listaConfigTaxaAdm[0],
      formapagamentotaxaadm: this.formapagamento,
      municipioprestacaotaxaadm: this.municipioprestacao,
      pessoa: this.idSeguradora,
      valortaxaadm: this.valor,
    };

    const response = this.$http({
      method: 'POST',
      data: dataToSend,
      url: this.nsjRouting.generate('crm_atcs_exclui_contrato_taxa_administrativa', { id: this.entity.negocio }),
    })
      .then((result) => {
        this.$rootScope.$broadcast('crm_atcs_exclui_contrato_taxa_administrativa_success_atualiza_atc');
        this.toaster.pop({
          type: 'success',
          title: 'Sucesso ao cancelar faturamento!',
        });
        this.close();
      })
      .catch((error) => {
        this.toaster.pop({
          type: 'error',
          title: error.data?.message != null ? error.data.message : error,
        });
        this.close();
      });
  }

  /**
   * Abre modal que confirma a geração do faturamento
   */
  abrirModalGerarFaturamento(taxaAdm: any) {
    let modal = this.ModalGerarFaturamentoService.open(taxaAdm);
    modal.result
      .then((response: any) => {})
      .catch((error: any) => {
        if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
          this.toaster.pop({
            type: 'error',
            title: error,
          });
        }
      });
  }
}

export class TaxaAdministrativaModalService {
  static $inject = ['$uibModal', 'nsjRouting', '$http', '$rootScope'];

  constructor(public $uibModal: any, public nsjRouting: any, public $http: any, public $rootScope: angular.IScope) {}

  open(parameters: any) {
    return this.$uibModal.open({
      template: require('./../../Crm/Atcs/taxa-administrativa-modal.html'),
      controller: 'TaxaAdministrativaModalController',
      controllerAs: 'tx_dmnstrtv_mdl_cntrllr',
      windowClass: 'modal-lg-wrapper',
      backdrop: 'static',
      resolve: {
        entity: async () => {
          let entity = parameters;

          let negocio = await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate('crm_atcs_get', { id: entity.negocio }, true),
          });

          negocio = negocio.data;

          // Buscando taxa administrativa filtrando por estabelecimento e seguradora
          negocio.listaConfigTaxaAdm = await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate(
              'crm_configuracoestaxasadministrativas_index',
              { estabelecimento: entity.estabelecimento.estabelecimento, seguradora: entity.cliente.cliente },
              true,
              true
            ),
          });

          negocio.listaConfigTaxaAdm = negocio.listaConfigTaxaAdm.data;

          return negocio;
        },
        constructors: () => {
          return parameters;
        },
      },
    });
  }
}

