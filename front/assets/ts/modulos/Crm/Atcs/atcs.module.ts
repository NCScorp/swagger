import angular from "angular";
import { CrmAtcsListController } from ".";
import { CrmAtcspendenciasUtilsService } from "../Atcspendencias/utils";
import { CrmAtcsFornecedoresAccordionController } from "./accordion-fornecedores";
import { CrmAtcsFullControllerSobrescrito } from "./atcs.full";
import { CrmAtcsFollowupsController } from "./atcsfollowups";
import { CrmAtcsDashboardController } from "./atendimentos";
import { CrmAtcsBaixardocumentoController } from "./baixardocumento.form";
import { CrmAtcsFormBaixardocumentoController, CrmAtcsFormBaixardocumentoService } from "./baixardocumento.modal";
import { crmAtcsBaixardocumento, crmAtcsContrato, crmAtcsContratoexcluir, crmAtcsDefault, crmAtcsEnviaratendimentoemail, crmAtcsRotasgoogledirections, crmAtcsVisualizacaocompletaShow } from "./components";
import { atcsRoutes } from "./config";
import { ConfirmacaoFecharPedidoModalController, ConfirmacaoFecharPedidoModalService } from "./confirmacao-fechar-pedido-modal";
import { CrmAtcsContratantesController } from "./contratantes";
import { CrmContratoPaganteAcoesModalController, CrmContratoPaganteAcoesModalService } from "./contrato-mais-pagante-acoes-modal";
import { CrmContratoMaisPaganteModalController, CrmContratoMaisPaganteModalService } from "./contrato-mais-pagante-modal";
import { CrmAtcsContratoController } from "./contrato.form";
import { CrmAtcsContratoexcluirController } from "./contratoexcluir.form";
import { CrmAtcsDefaultController } from "./default.form";
import { CrmAtcsDocumentosController } from "./secaodocumentos/secao-documentos.controller";
import { CrmAtcsDocumentosAutomaticosController } from "./documentosautomaticos";
import { CrmAtcsFormController } from "./edit";
import { CrmAtcsEnviaratendimentoemailController } from "./enviaratendimentoemail.form";
import { CrmAtcsFormEnviaratendimentoemailController, CrmAtcsFormEnviaratendimentoemailService } from "./enviaratendimentoemail.modal";
import { CrmAtcsFormContratoexcluirController, CrmAtcsFormContratoexcluirService } from "./excluiContrato.modal";
import { CrmAtcs } from "./factory";
import { CrmFichafinanceiraFormModalController, CrmFichafinanceiraFormService } from "./ficha-financeira-modal";
import { CrmAtcsFormContratoController, CrmAtcsFormContratoService } from "./geraContrato.modal";
import { ModalGerarFaturamentoController, ModalGerarFaturamentoService } from "./gerar-faturamento-modal";
import { CrmAtcsFormRotasgoogledirectionsController, CrmAtcsFormRotasgoogledirectionsService } from "./indexRotasGoogleDirections.modal";
import { EnviarAtcDocsEmailModalController, EnviarAtcDocsEmailModalService } from "./modal-enviaratcdocsemail";
import { EnviarAtcEmailModalController, EnviarAtcEmailModalService } from "./modal-enviaratcemail";
import { RotasSepultamentoModalController, RotasSepultamentoModalService } from "./modal-rotassepultamento";
import { CrmAtcsFormNewController } from "./new";
import { CrmOrcamentosAprovarModalController, CrmOrcamentosAprovarModalService } from "./orcamento-aprovar-modal";
import { CrmOrcamentosObservacoesSubModalController, CrmOrcamentosObservacoesSubModalService } from "./orcamento-observacoes-submodal";
import { CrmOrcamentosPreencherModalController, CrmOrcamentosPreencherModalService } from "./orcamento-preencher-modal";
import { CrmOrcamentosRenegociarSubModalController, CrmOrcamentosRenegociarSubModalService } from "./orcamento-renegociar-submodal";
import { CrmOrcamentosReprovarSubModalController, CrmOrcamentosReprovarSubModalService } from "./orcamento-reprovar-submodal";
import { CrmAtcsPedidosController } from "./pedidos";
import { CrmAtcsPendenciasController } from "./pendencias";
import { CrmAtcsProjetoAccordionController } from "./projeto-accordion";
import { CrmAtcsVisualizacaoShowController } from "./resumo-visualizacao";
import { CrmAtcsRotasgoogledirectionsController } from "./rotasGoogleDirections.form";
import { CrmAtcsFormShowController } from "./show";
import { TaxaAdministrativaModalController, TaxaAdministrativaModalService } from "./taxa-administrativa-modal";
import { CrmAtcsVisualizacaocompletaShowController } from "./visualizacaocompleta.form.show";
import { CrmAtcsDocumentosVisualizarModalService, CrmAtcsDocumentosVisualizarModalController } from "./secaodocumentos/visualizar/modal";
import { CrmModalAtcDocumentosLimparService, CrmModalAtcDocumentosLimparController } from "./secaodocumentos/modal-limpar-documento/modal";
import { CrmAtcsDocumentosInformacoesAdicionaisModalService, CrmAtcsDocumentosInformacoesAdicionaisModalController } from "./secaodocumentos/informacoesadicionais/modal";
import { SolicitarOrcamentoEmailModalService, SolicitarOrcamentoEmailModalController } from "./modal-solicitar-orcamento-email/modal-solicitar-orcamento-email";
import { ModalExclusaoDocumentoService } from "./secaodocumentos/modal-exclusao-documento";
import { ModalExclusaoDocumentoController } from "./secaodocumentos/modal-exclusao-documento";
import { ModalConfirmacaoHistoricoController, ModalConfirmacaoHistoricoService } from "../Atcsfollowups/modal-confirmacao-historico";
import { ConfirmacaoFinalizarAtendimentoModalController, ConfirmacaoFinalizarAtendimentoModalService } from "./confirmacao-finalizar-atendimento-modal";
import { ModalVisualizarMaisContatosController, ModalVisualizarMaisContatosService } from "./modal-visualizar-mais-contatos/modal-visualizar-mais-contatos";

export const AtcsModule = angular.module('atcsModule', [])
    .controller('CrmAtcsFornecedoresAccordionController', CrmAtcsFornecedoresAccordionController)
    .controller('CrmAtcsController', CrmAtcsFullControllerSobrescrito)
    .controller('CrmAtcsFollowupsController', CrmAtcsFollowupsController)
    .controller('CrmAtcsDashboardController', CrmAtcsDashboardController)
    .controller('CrmAtcsBaixardocumentoController', CrmAtcsBaixardocumentoController)
    .controller('CrmAtcsFormBaixardocumentoController', CrmAtcsFormBaixardocumentoController)
    .component('crmAtcsVisualizacaocompletaShow', crmAtcsVisualizacaocompletaShow)
    .component('crmAtcsDefault', crmAtcsDefault)
    .component('crmAtcsContrato', crmAtcsContrato)
    .component('crmAtcsContratoexcluir', crmAtcsContratoexcluir)
    .component('crmAtcsBaixardocumento', crmAtcsBaixardocumento)
    .component('crmAtcsEnviaratendimentoemail', crmAtcsEnviaratendimentoemail)
    .component('crmAtcsRotasgoogledirections', crmAtcsRotasgoogledirections)
    .service('CrmAtcsFormBaixardocumentoService', CrmAtcsFormBaixardocumentoService)
    .service('ConfirmacaoFecharPedidoModalService', ConfirmacaoFecharPedidoModalService)
    .controller('ConfirmacaoFecharPedidoModalController', ConfirmacaoFecharPedidoModalController)
    .service('SolicitarOrcamentoEmailModalService', SolicitarOrcamentoEmailModalService)
    .controller('SolicitarOrcamentoEmailModalController', SolicitarOrcamentoEmailModalController)
    .service('CrmAtcs', CrmAtcs)
    .controller('CrmAtcsContratantesController', CrmAtcsContratantesController)
    .service('CrmContratoPaganteAcoesModalService', CrmContratoPaganteAcoesModalService)
    .controller('CrmContratoPaganteAcoesModalController', CrmContratoPaganteAcoesModalController)
    .service('CrmContratoMaisPaganteModalService', CrmContratoMaisPaganteModalService)
    .controller('CrmContratoMaisPaganteModalController', CrmContratoMaisPaganteModalController)
    .controller('CrmAtcsContratoController', CrmAtcsContratoController)
    .controller('CrmAtcsContratoexcluirController', CrmAtcsContratoexcluirController)
    .controller('CrmAtcsDefaultController', CrmAtcsDefaultController)
    .controller('CrmAtcsDocumentosController', CrmAtcsDocumentosController)
    .controller('CrmAtcsDocumentosVisualizarModalController',CrmAtcsDocumentosVisualizarModalController)
    .service('CrmAtcsDocumentosVisualizarModalService', CrmAtcsDocumentosVisualizarModalService)
    .service('CrmModalAtcDocumentosLimparService', CrmModalAtcDocumentosLimparService)
    .controller('CrmModalAtcDocumentosLimparController', CrmModalAtcDocumentosLimparController)
    .controller('CrmAtcsDocumentosInformacoesAdicionaisModalController', CrmAtcsDocumentosInformacoesAdicionaisModalController)
    .service('CrmAtcsDocumentosInformacoesAdicionaisModalService', CrmAtcsDocumentosInformacoesAdicionaisModalService)
    .controller('CrmAtcsDocumentosAutomaticosController', CrmAtcsDocumentosAutomaticosController)
    .controller('CrmAtcsFormController', CrmAtcsFormController)
    .controller('CrmAtcsEnviaratendimentoemailController', CrmAtcsEnviaratendimentoemailController)
    .controller('CrmAtcsFormEnviaratendimentoemailController', CrmAtcsFormEnviaratendimentoemailController)
    .service('CrmAtcsFormEnviaratendimentoemailService', CrmAtcsFormEnviaratendimentoemailService)
    .controller('CrmAtcsFormContratoexcluirController', CrmAtcsFormContratoexcluirController)
    .service('CrmAtcsFormContratoexcluirService', CrmAtcsFormContratoexcluirService)
    .controller('CrmFichafinanceiraFormModalController', CrmFichafinanceiraFormModalController)
    .service('CrmFichafinanceiraFormService', CrmFichafinanceiraFormService)
    .controller('CrmAtcsFormContratoController', CrmAtcsFormContratoController)
    .service('CrmAtcsFormContratoService', CrmAtcsFormContratoService)
    .service('ModalGerarFaturamentoService', ModalGerarFaturamentoService)
    .controller('ModalGerarFaturamentoController', ModalGerarFaturamentoController)
    .controller('CrmAtcsListController', CrmAtcsListController)
    .controller('CrmAtcsFormRotasgoogledirectionsController', CrmAtcsFormRotasgoogledirectionsController)
    .service('CrmAtcsFormRotasgoogledirectionsService', CrmAtcsFormRotasgoogledirectionsService)
    .service('EnviarAtcDocsEmailModalService', EnviarAtcDocsEmailModalService)
    .controller('EnviarAtcDocsEmailModalController', EnviarAtcDocsEmailModalController)
    .service('EnviarAtcEmailModalService', EnviarAtcEmailModalService)
    .controller('EnviarAtcEmailModalController', EnviarAtcEmailModalController)
    .service('RotasSepultamentoModalService', RotasSepultamentoModalService)
    .controller('RotasSepultamentoModalController', RotasSepultamentoModalController)
    .controller('CrmAtcsFormNewController', CrmAtcsFormNewController)
    .service('CrmOrcamentosAprovarModalService', CrmOrcamentosAprovarModalService)
    .controller('CrmOrcamentosAprovarModalController', CrmOrcamentosAprovarModalController)
    .service('CrmOrcamentosObservacoesSubModalService', CrmOrcamentosObservacoesSubModalService)
    .controller('CrmOrcamentosObservacoesSubModalController', CrmOrcamentosObservacoesSubModalController)
    .service('CrmOrcamentosPreencherModalService', CrmOrcamentosPreencherModalService)
    .controller('CrmOrcamentosPreencherModalController', CrmOrcamentosPreencherModalController)
    .service('CrmOrcamentosRenegociarSubModalService', CrmOrcamentosRenegociarSubModalService)
    .controller('CrmOrcamentosRenegociarSubModalController', CrmOrcamentosRenegociarSubModalController)
    .service('CrmOrcamentosReprovarSubModalService', CrmOrcamentosReprovarSubModalService)
    .controller('CrmOrcamentosReprovarSubModalController', CrmOrcamentosReprovarSubModalController)
    .controller('CrmAtcsPedidosController', CrmAtcsPedidosController)
    .controller('CrmAtcsPendenciasController', CrmAtcsPendenciasController)
    .service('CrmAtcspendenciasUtilsService', CrmAtcspendenciasUtilsService)
    .controller('CrmAtcsProjetoAccordionController', CrmAtcsProjetoAccordionController)
    .controller('CrmAtcsVisualizacaoShowController', CrmAtcsVisualizacaoShowController)
    .controller('CrmAtcsRotasgoogledirectionsController', CrmAtcsRotasgoogledirectionsController)
    .controller('CrmAtcsFormShowController', CrmAtcsFormShowController)
    .service('TaxaAdministrativaModalService', TaxaAdministrativaModalService)
    .controller('TaxaAdministrativaModalController', TaxaAdministrativaModalController)
    .controller('CrmAtcsVisualizacaocompletaShowController', CrmAtcsVisualizacaocompletaShowController)
    .service('ModalExclusaoDocumentoService', ModalExclusaoDocumentoService)
    .service('ModalConfirmacaoHistoricoService', ModalConfirmacaoHistoricoService)
    .controller('ModalConfirmacaoHistoricoController', ModalConfirmacaoHistoricoController)
    .controller('ModalExclusaoDocumentoController', ModalExclusaoDocumentoController)
    .service('ConfirmacaoFinalizarAtendimentoModalService', ConfirmacaoFinalizarAtendimentoModalService)
    .controller('ConfirmacaoFinalizarAtendimentoModalController', ConfirmacaoFinalizarAtendimentoModalController)
    .service('ModalVisualizarMaisContatosService', ModalVisualizarMaisContatosService)
    .controller('ModalVisualizarMaisContatosController', ModalVisualizarMaisContatosController)
    .config(atcsRoutes)
    .constant('FIELDS_CrmAtcs', [
        {
            value: 'nome',
            label: 'Nome',
            type: 'string',
            style: 'title',
            copy: '',
        },
        {
            value: 'status',
            label: 'Status',
            type: 'options',
            style: 'label',
            copy: '',
            options: { 'Novo': 'entity.status == "0"', 'Em Atendimento': 'entity.status == "1"', 'Processando': 'entity.status == "2"', 'Finalizado': 'entity.status == "3"', 'Cancelado': 'entity.status == "4"', },
            label_class: '{"label-info": entity.status == "0", "label-default": entity.status == "1", "label-warning": entity.status == "2", "label-success": entity.status == "3", "label-danger": entity.status == "4", }',
        },
        {
            value: 'datacriacao',
            label: 'Data de criação',
            type: 'datetime',
            style: 'title',
            copy: '',
        },
        {
          value: 'created_by.nome',
          label: 'Criado por',
          type: 'json',
          style: 'title',
          copy: '',
        },
        {
            value: 'codigo',
            label: 'Código',
            type: 'string',
            style: 'default',
            copy: '',
        },
        {
            value: 'possuiseguradora',
            label: 'Com Seguradora',
            type: 'options',
            style: 'default',
            copy: '',
            options: { 'Não': 'entity.possuiseguradora == "0"', 'Sim': 'entity.possuiseguradora == "1"', },
        },

        {
            value: 'dataedicao',
            label: 'Data de atualização',
            type: 'datetime',
            style: 'default',
            copy: '',
        },

    ])
    .constant('OPTIONS_CrmAtcs', { 'negociopai': 'Atendimento Comercial principal', 'localizacaopaisnome': 'Localização do País', 'localizacaoestadonome': 'Localização do Estado', 'localizacaomunicipionome': 'Localização do Município', 'status': 'Situação', 'possuiseguradora': 'Possui Seguradora', })
    .constant('MAXOCCURS_CrmAtcs', {})
    .constant('SELECTS_CrmAtcs', { 'status': { '0': 'Novo', '1': 'Em Atendimento', '2': 'Processando', '3': 'Finalizado', '4': 'Cancelado', }, 'possuiseguradora': { '0': 'Não', '1': 'Sim', }, })
    .run(['$rootScope', 'FIELDS_CrmAtcs', 'OPTIONS_CrmAtcs', 'MAXOCCURS_CrmAtcs', 'SELECTS_CrmAtcs',
        ($rootScope: any, FIELDS_CrmAtcs: object, OPTIONS_CrmAtcs: object, MAXOCCURS_CrmAtcs: object, SELECTS_CrmAtcs: object) => {
            $rootScope.OPTIONS_CrmAtcs = OPTIONS_CrmAtcs;
            $rootScope.MAXOCCURS_CrmAtcs = MAXOCCURS_CrmAtcs;
            $rootScope.SELECTS_CrmAtcs = SELECTS_CrmAtcs;
            $rootScope.FIELDS_CrmAtcs = FIELDS_CrmAtcs;
        }])
    .name


