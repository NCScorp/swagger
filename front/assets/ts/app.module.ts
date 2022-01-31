import angular from 'angular';
import { AppController } from './app.controller';
import { appRoutes } from './app.routes';
import { ConfiguracaoService } from './inicializacao/configuracao.service';
import { camposCustomizadosModule } from './modulos/Commons/camposcustomizados/camposcustomizados.module';
import { GooglePlacesModule } from './modulos/Commons/googlemaps/places.module';
import { nsDropzone } from './modulos/Commons/nsdropzone/nsdropzone.module';
import { nsjIndice } from './modulos/Commons/nsjindice/nsjindice.module';
import { treeGrid } from './modulos/Commons/nsTreeGrid/treeGrid';
import { TreeDnDModule } from './modulos/Commons/tree';
import { UtilsModule } from './modulos/Commons/utils/utils.module';
import { AtcClienteFornecedoresContatosModule } from './modulos/Crm/Atcclientefornecedorescontatos/Atcclientefornecedorescontatos.module';
import { CrmAtcsFullModule } from './modulos/Crm/Atcs/atcs.full';
import { AtcsModule } from './modulos/Crm/Atcs/atcs.module';
import { CrmAtcsDashboardModule } from './modulos/Crm/Atcs/atendimentos';
import { fichaFinanceiraModule } from './modulos/Crm/Atcs/Fichafinanceira/fichafinanceira.module';
import { CrmAtcsPendenciasModule } from './modulos/Crm/Atcs/pendencias';
import { atcsAreasModule } from './modulos/Crm/Atcsareas/atcs-areas.module';
import { atcsAreasPendenciasModule } from './modulos/Crm/Atcsareaspendencias/atcs-areas-pendencias.module';
import { AtcsAreasPendenciasListasModule } from './modulos/Crm/Atcsareaspendenciaslistas/Atcsareaspendenciaslistas.module';
import { atcsConfiguracoesDocumentosModule } from './modulos/Crm/Atcsconfiguracoesdocumentos/atcs-configuracoes-documentos.module';
import { AtcsconfiguracoesdocumentositensModule } from './modulos/Crm/Atcsconfiguracoesdocumentositens/atcs-configuracoes-documentos-itens.module';
import { AtcsconfiguracoestemplatesemailsModule } from './modulos/Crm/Atcsconfiguracoestemplatesemails/atcs-configuracoes-templates-emails.module';
import { AtcsdadosseguradorasModule } from './modulos/Crm/Atcsdadosseguradoras/atcs-dados-seguradoras.module';
import { AtcsdocumentosModule } from './modulos/Crm/Atcsdocumentos/atcs-documentos.module';
import { AtcsfollowupsModule } from './modulos/Crm/Atcsfollowups/atcs-followups.module';
import { AtcsfollowupsanexosModule } from './modulos/Crm/Atcsfollowupsanexos/atcs-followups-anexos.module';
import { AtcshistoricoModule } from './modulos/Crm/Atcshistorico/atcs-historico.module';
import { AtcspendenciasModule } from './modulos/Crm/Atcspendencias/atcs-pendencias.module';
import { AtcspendenciaslistasModule } from './modulos/Crm/Atcspendenciaslistas/atcs-pendencias-listas.module';
import { AtcsrelacionadosModule } from './modulos/Crm/Atcsrelacionados/atcs-relacionados.module';
import { AtcsresponsaveisfinanceirosModule } from './modulos/Crm/Atcsresponsaveisfinanceiros/atcs-responsaveis-financeiros.module';
import { AtcstiposdocumentosrequisitantesModule } from './modulos/Crm/Atcstiposdocumentosrequisitantes/atcs-tipos-documentos-requisitantes.module';
import { BuscarservicoseprodutosModule } from './modulos/Crm/Buscarservicoseprodutos/buscar-servicos-produtos.module';
import { composicoesModule } from './modulos/Crm/Composicoes/composicoes.module';
import { ComposicoesfamiliasModule } from './modulos/Crm/Composicoesfamilias/composicoes-familias.module';
import { ComposicoesfuncoesModule } from './modulos/Crm/Composicoesfuncoes/composicoes-funcoes.module';
import { ConfiguracoestaxasadministrativasModule } from './modulos/Crm/Configuracoestaxasadministrativas/configuracoes-taxas-administrativas.module';
import { DashboardatendimentospendenciasModule } from './modulos/Crm/Dashboardatendimentospendencias/dashboard-atendimentos-pendencias.module';
import { FornecedoresenvolvidosModule } from './modulos/Crm/Fornecedoresenvolvidos/Fornecedoresenvolvidos.module';
import { FornecedoresenvolvidosanexosModule } from './modulos/Crm/Fornecedoresenvolvidosanexos/fornecedores-envolvidos-anexos.module';
import { FornecedorespedidoselecaoModule } from './modulos/Crm/Fornecedorespedidoselecao/fornecedores-pedido-selecao.module';
import { HistoricoatcsModule } from './modulos/Crm/Historicoatcs/historico-atcs.module';
import { HistoricospadraoModule } from './modulos/Crm/Historicospadrao/historicos-padrao.module';
import { ListadavezconfiguracoesModule } from './modulos/Crm/Listadavezconfiguracoes/Listadavez-configuracoes.module';
import { ListadavezregrasModule } from './modulos/Crm/Listadavezregras/listadavez-regras.module';
import { ListadavezregrasvaloresModule } from './modulos/Crm/Listadavezregrasvalores/Listadavez-regras-valores.module';
import { ListadavezvendedoresModule } from './modulos/Crm/Listadavezvendedores/listadavez-vendedores.module';
import { ListadavezvendedoresitensModule } from './modulos/Crm/Listadavezvendedoresitens/listadavez-vendedores-itens.module';
import { MalotesModule } from './modulos/Crm/Malotes/malotes.module';
import { malotesDocumentosModule } from './modulos/Crm/Malotesdocumentos/malotes-documentos.module';
import { midiasModule } from './modulos/Crm/Midias/midias.module';
import { MotivosdesqualificacoesprenegociosModule } from './modulos/Crm/Motivosdesqualificacoesprenegocios/motivos-desqualificacoes-prenegocios.module';
import { NegociosModule } from './modulos/Crm/Negocios/negocios.module';
import { NegocioscontatosModule } from './modulos/Crm/Negocioscontatos/negocios-contatos.module';
import { NegociosoperacoesModule } from './modulos/Crm/Negociosoperacoes/negocios-operacoes.module';
import { OrcamentosModule } from './modulos/Crm/Orcamentos/orcamentos.module';
import { paineisModule } from './modulos/Crm/Paineis/paineis.module';
import { PromocoesleadsModule } from './modulos/Crm/Promocoesleads/promocoes-leads.module';
import { PropostasModule } from './modulos/Crm/Propostas/propostas.module';
import { PropostascapitulosModule } from './modulos/Crm/Propostascapitulos/propostas-capitulos.module';
import { PropostasitensModule } from './modulos/Crm/Propostasitens/propostas-itens.module';
import { PropostasitensenderecosModule } from './modulos/Crm/Propostasitensenderecos/propostas-itens-enderecos.module';
import { PropostasitensfamiliasModule } from './modulos/Crm/Propostasitensfamilias/propostas-itens-familias.module';
import { PropostasitensfuncoesModule } from './modulos/Crm/Propostasitensfuncoes/propostas-itens-funcoes.module';
import { ResponsabilidadesfinanceirasModule } from './modulos/Crm/Responsabilidadesfinanceiras/responsabilidades-financeiras.module';
import { ResponsabilidadesfinanceirasvaloresModule } from './modulos/Crm/Responsabilidadesfinanceirasvalores/responsabilidades-financeiras-valores.module';
import { SegmentosatuacaoModule } from './modulos/Crm/Segmentosatuacao/segmento-satuacao.module';
import { SituacoesprenegociosModule } from './modulos/Crm/Situacoesprenegocios/situacoes-prenegocios.module';
import { TemplatescomposicoesfamiliasModule } from './modulos/Crm/Templatescomposicoesfamilias/templates-composicoes-familias.module';
import { TemplatescomposicoesfuncoesModule } from './modulos/Crm/Templatescomposicoesfuncoes/templates-composicoes-funcoes.module';
import { TemplatesemailadvertirprestadorModule } from './modulos/Crm/Templatesemailadvertirprestador/templates-email-advertir-prestador.module';
import { TemplatespropostasModule } from './modulos/Crm/Templatespropostas/templates-propostas.module';
import { TemplatespropostascapitulosModule } from './modulos/Crm/Templatespropostascapitulos/templates-propostas-capitulos.module';
import { TemplatespropostascapituloscomposicoesModule } from './modulos/Crm/Templatespropostascapituloscomposicoes/templates-propostas-capitulos-composicoes.module';
import { TemplatespropostasdocumentosModule } from './modulos/Crm/Templatespropostasdocumentos/templates-propostas-documentos.module';
import { TemplatespropostasgruposModule } from './modulos/Crm/Templatespropostasgrupos/templates-propostas-grupos.module';
import { TiposacionamentosModule } from './modulos/Crm/Tiposacionamentos/tipos-acionamentos.module';
import { VinculosModule } from './modulos/Crm/Vinculos/vinculos.module';
import { FamiliasModule } from './modulos/Estoque/Familias/familias.module';
import { UnidadesModule } from './modulos/Estoque/Unidades/unidades.module';
import { BancosModule } from './modulos/Financas/Bancos/bancos.module';
import { ContasModule } from './modulos/Financas/Contas/contas.module';
import { ContasfornecedoresModule } from './modulos/Financas/Contasfornecedores/contas-fornecedores.module';
import { ContratosModule } from './modulos/Financas/Contratos/contratos.module';
import { ItenscontratosModule } from './modulos/Financas/Itenscontratos/itens-contratos.module';
import { CustosModule } from './modulos/Gp/Custos/custos.module';
import { FuncoesModule } from './modulos/Gp/Funcoes/funcoes.module';
import { FuncoescustosModule } from './modulos/Gp/Funcoescustos/funcoes-custos.module';
import { ProjetosescopoModule } from './modulos/Gp/Projetosescopo/projetos-escopo.module';
import { TarefasModule } from './modulos/Gp/Tarefas/tarefas.module';
import { TiposprojetosModule } from './modulos/Gp/Tiposprojetos/tipos-projetos.module';
import { AdvertenciasModule } from './modulos/Ns/Advertencias/advertencias.module';
import { NsCamposcustomizadosModule } from './modulos/Ns/Camposcustomizados/ns-campos-customizados.module';
import { CidadesestrangeirasModule } from './modulos/Ns/Cidadesestrangeiras/cidades-estrangeiras.module';
import { CidadesinfofunerariasfornecedoresModule } from './modulos/Ns/Cidadesinfofunerariasfornecedores/cidades-info-funerarias-fornecedores.module';
import { CidadesinformacoesfunerariasModule } from './modulos/Ns/Cidadesinformacoesfunerarias/cidades-informacoes-funerarias.module';
import { ClientesModule } from './modulos/Ns/Clientes/clientes.module';
import { ClientesdocumentosModule } from './modulos/Ns/Clientesdocumentos/clientes-documentos.module';
import { CnaesModule } from './modulos/Ns/Cnaes/cnaes.module';
import { CnaestiposatividadesModule } from './modulos/Ns/Cnaestiposatividades/cnaes-tipos-atividades.module';
import { ContatosModule } from './modulos/Ns/Contatos/contatos.module';
import { DocumentosfopModule } from './modulos/Ns/Documentosfop/documentos-fop.module';
import { DocumentosnecessariosModule } from './modulos/Ns/Documentosnecessarios/documentos-necessarios.module';
import { EnderecosModule } from './modulos/Ns/Enderecos/enderecos.module';
import { EstabelecimentosModule } from './modulos/Ns/Estabelecimentos/estabelecimentos.module';
import { EstadosModule } from './modulos/Ns/Estados/estados.module';
import { FollowupsnegociosModule } from './modulos/Ns/Followupsnegocios/followups-negocios.module';
import { FormaspagamentosModule } from './modulos/Ns/Formaspagamentos/formas-pagamentos.module';
import { FornecedoresModule } from './modulos/Ns/Fornecedores/fornecedores.module';
import { FornecedoressuspensosModule } from './modulos/Ns/Fornecedoressuspensos/fornecedores-suspensos.module';
import { HistoricofornecedoresModule } from './modulos/Ns/Historicofornecedores/historico-fornecedores.module';
import { LayoutsdocumentosimpressosModule } from './modulos/Ns/Layoutsdocumentosimpressos/layouts-documentos-impressos.module';
import { MunicipiosModule } from './modulos/Ns/Municipios/municipios.module';
import { PaisesModule } from './modulos/Ns/Paises/paises.module';
import { PessoasLogoModule } from './modulos/Ns/Pessoas/Logos/pessoas-logo.module';
import { PessoastiposatividadesModule } from './modulos/Ns/Pessoastiposatividades/pessoas-tipos-atividades.module';
import { PrioridadesModule } from './modulos/Ns/Prioridades/prioridades.module';
import { ProspectsModule } from './modulos/Ns/Prospects/prospects.module';
import { TelefonesModule } from './modulos/Ns/Telefones/telefones.module';
import { TiposatividadesModule } from './modulos/Ns/Tiposatividades/tipos-atividades.module';
import { TiposdocumentosModule } from './modulos/Ns/Tiposdocumentos/tipos-documentos.module';
import { TiposlogradourosModule } from './modulos/Ns/Tiposlogradouros/tipos-logradouros.module';
import { VendedoresModule } from './modulos/Ns/Vendedores/vendedores.module';
import { ItensdefaturamentoModule } from './modulos/Servicos/Itensdefaturamento/itens-de-faturamento.module';
import { PessoasmunicipiosModule } from './modulos/Servicos/Pessoasmunicipios/pessoas-municipios.module';
import { ConfiguracoesModule } from './modulos/Web/Configuracoes/configuracoes.module';
import { nsjRoutingModule } from './nsjrouting/nsj-routing.module';
import { permissoes } from './permissoes/permissoes.module';
import { Usuarios } from './usuarios/usuarios.service';
import { FornecedoresdocumentosModule } from './modulos/Ns/Fornecedoresdocumentos/fornecedores-documentos.module';
import { changeSvgModule } from './modulos/Commons/changeSvg';
import { commonsModule } from './modulos/Commons/commons.module';
import { acordosterceirizacoesservicosModule } from './modulos/Crm/Acordosterceirizacoesservicos/acordos-terceirizacoes-servicos.module';
import { atcpreencherorcamentoModule } from './modulos/Crm/Atcs/preencher-orcamento/atc-preencher-orcamento.module';
import { CrmAtcContaPagarModule } from './modulos/Crm/Atcs/Atcscontaspagar/crmatccontapagar.module';
import { nsjTelaLista } from './shared/components/nsjtelalista/nsjtelalista.module';
import { ProjetosModule } from './modulos/Gp/Projetos/projetos.module'; 
import { EtapasModule } from './modulos/Gp/Etapas/etapas.module';
import { DiretorioContasModule } from './modulos/Diretorio/contas/contas.module';
import { TecnicosLogoModule } from './modulos/Servicos/Tecnicos/tecnicos-logo/tecnicos-logo.module';
import { TecnicosModule } from './modulos/Servicos/Tecnicos/tecnicos.module';
import { EstoqueLocaisModule } from './modulos/Estoque/Estoque-locais/estoque-locais.module';
import { EstoqueSaldoItensLocaisModule } from './modulos/Estoque/Estoque-saldo-itens-locais/estoque-saldo-itens-locais.module';
import { EstoqueItensModule } from './modulos/Estoque/Estoque-itens/estoque-itens.module';
import { DocumentosFopTemplatesModule } from './modulos/Ns/Documentos-fop-templates/documentos-fop-templates.module';
import { OperacoesDocumentosTemplatesModule } from './modulos/Servicos/Operacoes-documentos-templates/operacoes-documentos-templates.module';
import { NsSeriesModule } from './modulos/Ns/Series/series.module';
import { TiposServicosModule } from './modulos/Servicos/Tipos-servicos/tipos-servicos.module';
import { TiposOrdensServicosModule } from './modulos/Servicos/Tipos-ordens-servicos/tipos-ordens-servicos.module';
import { TiposManutencoesModule } from './modulos/Servicos/Tipos-manutencoes/tipos-manutencoes.module';
import { EquipesModule } from './modulos/Servicos/Equipes/equipes.module';
import { OperacoesOrdensServicosModule } from './modulos/Servicos/Operacoes-ordens-servicos/operacoes-ordens-servicos.module';
import { EstoqueVeiculosModule } from './modulos/Estoque/Veiculos/veiculos.module';
import { TemplatesOrdemServicoModule } from './modulos/Servicos/Templates-os/templates-os.module';
import { ServicosTecnicosModule } from './modulos/Servicos/Servicos-tecnicos/servicos-tecnicos.module';

//Contém import de depenpêndencias do projeto
// import  './app_index';

export const app = angular
  .module('main', [
    'ui.router',
    'ui.bootstrap',
    'ui.select',
    'ngSanitize',
    'mdaUiSelect',
    'ngMessages',
    'objectList',
    'ngFileUpload',
    'angular-file-input',
    'multipleDatePicker',
    'angularMoment',
    'angular.filter',
    'toaster',
    'convertToNumber',
    'ntt.TreeDnD',
    'filters',
    'ui.mask',
    'ui.tree',
    'checklist-model',
    'ngCpfCnpj',
    'dateInput',
    'nasajon-ui',
    'ng-sortable',
    'ngTagsInput',
    // dateRange,
    changeSvgModule,
    TreeDnDModule,
    camposCustomizadosModule,
    GooglePlacesModule,
    nsjRoutingModule,
    nsDropzone,
    treeGrid,
    nsjIndice,
    commonsModule,
    permissoes,
    UtilsModule,
    CrmAtcsFullModule,
    CrmAtcsDashboardModule,
    CrmAtcsPendenciasModule,
    DashboardatendimentospendenciasModule,
    AtcClienteFornecedoresContatosModule,
    AtcsModule,
    atcsAreasModule,
    atcsAreasPendenciasModule,
    fichaFinanceiraModule,
    acordosterceirizacoesservicosModule,
    atcpreencherorcamentoModule,
    AtcsAreasPendenciasListasModule,
    atcsConfiguracoesDocumentosModule,
    AtcsconfiguracoestemplatesemailsModule,
    AtcsconfiguracoesdocumentositensModule,
    AtcsdadosseguradorasModule,
    AtcsdocumentosModule,
    AtcsfollowupsModule,
    AtcsfollowupsanexosModule,
    AtcshistoricoModule,
    AtcspendenciasModule,
    AtcspendenciaslistasModule,
    AtcsrelacionadosModule,
    AtcsresponsaveisfinanceirosModule,
    AtcstiposdocumentosrequisitantesModule,
    BuscarservicoseprodutosModule,
    composicoesModule,
    ComposicoesfamiliasModule,
    ComposicoesfuncoesModule,
    ConfiguracoestaxasadministrativasModule,
    FornecedoresenvolvidosModule,
    FornecedoresenvolvidosanexosModule,
    FornecedorespedidoselecaoModule,
    HistoricoatcsModule,
    HistoricospadraoModule,
    ListadavezconfiguracoesModule,
    ListadavezregrasModule,
    ListadavezregrasvaloresModule,
    ListadavezvendedoresModule,
    ListadavezvendedoresitensModule,
    MalotesModule,
    malotesDocumentosModule,
    midiasModule,
    paineisModule,
    MotivosdesqualificacoesprenegociosModule,
    NegociosModule,
    NegocioscontatosModule,
    NegociosoperacoesModule,
    OrcamentosModule,
    PromocoesleadsModule,
    PropostasModule,
    PropostascapitulosModule,
    PropostasitensModule,
    PropostasitensenderecosModule,
    PropostasitensfamiliasModule,
    PropostasitensfuncoesModule,
    ResponsabilidadesfinanceirasModule,
    ResponsabilidadesfinanceirasvaloresModule,
    SegmentosatuacaoModule,
    SituacoesprenegociosModule,
    TemplatescomposicoesfamiliasModule,
    TemplatescomposicoesfuncoesModule,
    TemplatesemailadvertirprestadorModule,
    TemplatespropostasModule,
    TemplatespropostascapitulosModule,
    TemplatespropostascapituloscomposicoesModule,
    TemplatespropostasdocumentosModule,
    TemplatespropostasgruposModule,
    TiposacionamentosModule,
    VinculosModule,
    FamiliasModule,
    UnidadesModule,
    BancosModule,
    ContasModule,
    ContasfornecedoresModule,
    ContratosModule,
    ItenscontratosModule,
    ItensdefaturamentoModule,
    PessoasmunicipiosModule,
    ConfiguracoesModule,
    CustosModule,
    FuncoesModule,
    FuncoescustosModule,
    ProjetosescopoModule,
    TarefasModule,
    TiposprojetosModule,
    ClientesModule,
    AdvertenciasModule,
    NsCamposcustomizadosModule,
    CidadesestrangeirasModule,
    CidadesinfofunerariasfornecedoresModule,
    CidadesinformacoesfunerariasModule,
    ClientesdocumentosModule,
    CnaesModule,
    VendedoresModule,
    TiposatividadesModule,
    CnaestiposatividadesModule,
    ContatosModule,
    DocumentosfopModule,
    DocumentosnecessariosModule,
    EnderecosModule,
    EstabelecimentosModule,
    EstadosModule,
    FollowupsnegociosModule,
    FormaspagamentosModule,
    FornecedoresModule,
    FornecedoressuspensosModule,
    HistoricofornecedoresModule,
    LayoutsdocumentosimpressosModule,
    MunicipiosModule,
    PaisesModule,
    PessoasLogoModule,
    PessoastiposatividadesModule,
    PrioridadesModule,
    ProspectsModule,
    TelefonesModule,
    TiposdocumentosModule,
    TiposlogradourosModule,
    FornecedoresdocumentosModule,
    CrmAtcContaPagarModule,
    nsjTelaLista,
    ProjetosModule,
    EtapasModule,
    DiretorioContasModule,
    TecnicosLogoModule,
    TecnicosModule,
    EstoqueLocaisModule,
    EstoqueSaldoItensLocaisModule,
    EstoqueItensModule,
    DocumentosFopTemplatesModule,
    OperacoesDocumentosTemplatesModule,
    NsSeriesModule,
    TiposServicosModule,
    TiposOrdensServicosModule,
    TiposManutencoesModule,
    EquipesModule,
    OperacoesOrdensServicosModule,
    EstoqueVeiculosModule,
    TemplatesOrdemServicoModule,
    ServicosTecnicosModule
  ])
  .config(appRoutes)
  .service('Usuarios', Usuarios)
  .service('ConfiguracaoService', ConfiguracaoService)
  .controller('AppController', AppController)
  .constant('angularMomentConfig', {
    timezone: 'America/Sao_Paulo',
  }).name;
