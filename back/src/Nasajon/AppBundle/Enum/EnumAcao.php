<?php

namespace Nasajon\AppBundle\Enum;
/**
 * Enum para ser usado nas ações do Voter
 */
abstract class EnumAcao {
  
  const ATCS_INDEX = 'crm_atcs_index';
  const ATCS_CREATE = 'crm_atcs_create';
  const ATCS_PUT = 'crm_atcs_put';
  const ATCS_GET = 'crm_atcs_get';
  const FORNECEDORESENVOLVIDOS_CREATE = 'crm_fornecedoresenvolvidos_create';
  const FORNECEDORESENVOLVIDOS_DELETE = 'crm_fornecedoresenvolvidos_delete';

  const TEMPLATESEMAILADVERTIRPRESTADOR_CREATE  = 'crm_templatesemailadvertirprestador_create';
  const TEMPLATESEMAILADVERTIRPRESTADOR_PUT     = 'crm_templatesemailadvertirprestador_put';
  const TEMPLATESEMAILADVERTIRPRESTADOR_DELETE  = 'crm_templatesemailadvertirprestador_delete';

  const FORNECEDORES_INDEX = 'ns_fornecedores_index';
  const FORNECEDORES_CREATE = 'ns_fornecedores_create';
  const FORNECEDORES_PUT = 'ns_fornecedores_put';
  const FORNECEDORES_SUSPENDER = 'ns_fornecedores_suspender';
  const FORNECEDORES_ADVERTIR = 'ns_fornecedores_fornecedoradvertir';
  const FORNECEDORES_REATIVAR = 'ns_fornecedores_fornecedorreativar';
  const FORNECEDORES_GET = 'ns_fornecedores_get';
  const FORNECEDORESSUSPENSOS_INDEX = 'ns_fornecedoressuspensos_index';
  const CLIENTES_INDEX = 'ns_clientes_index';
  const CLIENTES_CREATE = 'ns_clientes_create';
  const CLIENTES_PUT = 'ns_clientes_put';
  const COMPOSICOES_INDEX = 'crm_composicoes_index';
  const COMPOSICOES_CREATE = 'crm_composicoes_create';
  const COMPOSICOES_PUT = 'crm_composicoes_put';
  const DOCUMENTOS_INDEX = 'ns_tiposdocumentos_index';
  const DOCUMENTOS_CREATE = 'ns_tiposdocumentos_create';
  const DOCUMENTOS_PUT = 'ns_tiposdocumentos_put';
  const VINCULOS_INDEX = 'crm_vinculos_index';
  const VINCULOS_CREATE = 'crm_vinculos_create';
  const VINCULOS_PUT = 'crm_vinculos_put';
  const MIDIAS_INDEX = 'crm_midias_index';
  const MIDIAS_CREATE = 'crm_midias_create';
  const MIDIAS_PUT = 'crm_midias_put';
  const TIPOSACIONAMENTOS_INDEX = 'crm_tiposacionamentos_index';
  const TIPOSACIONAMENTOS_GET = 'crm_tiposacionamentos_get';
  const TIPOSACIONAMENTOS_CREATE = 'crm_tiposacionamentos_create';
  const TIPOSACIONAMENTOS_PUT = 'crm_tiposacionamentos_put';
  const TIPOSACIONAMENTOS_DELETE = 'crm_tiposacionamentos_delete';
  const ATCSAREAS_INDEX = 'crm_atcsareas_index';
  const ATCSAREAS_CREATE = 'crm_atcsareas_create';
  const ATCSAREAS_PUT = 'crm_atcsareas_put';
  const ORCAMENTOS_CREATE = 'crm_orcamentos_create';
  const ORCAMENTOS_APROVAR = 'crm_orcamentos_aprovar';
  const ORCAMENTOS_ENVIAR = 'crm_orcamentos_enviar';
  const ORCAMENTOS_REABRIR = 'crm_orcamentos_reabrir';
  const UNIDADES_INDEX = 'estoque_unidades_index';
  const UNIDADES_CREATE = 'estoque_unidades_create';
  const UNIDADES_PUT = 'estoque_unidades_put';
  const TIPOSATIVIDADES_INDEX = 'ns_tiposatividades_index';
  const TIPOSATIVIDADES_CREATE = 'ns_tiposatividades_create';
  const TIPOSATIVIDADES_PUT = 'ns_tiposatividades_put';
  const ADVERTENCIAS_ARQUIVAR = 'ns_advertencias_arquivar';
  const ADVERTENCIAS_EXCLUIR = 'ns_advertencias_excluir';
  const PROPOSTASCAPITULOS_CREATE = 'crm_propostascapitulos_create';
  const PROPOSTASITENS_CREATE = 'crm_propostasitens_create';
  const PROPOSTASITENS_GET = 'crm_propostasitens_get';
  const PROPOSTASITENS_VINCULARFORNECEDOR = 'crm_propostasitens_vincularfornecedor';
  const PROPOSTASITENSFUNCOES_CREATE = 'crm_propostasitensfuncoes_create';
  const PROPOSTASITENSFAMILIAS_CREATE = 'crm_propostasitensfamilias_create';
  const PROPOSTASITENSFAMILIAS_PUT = 'crm_propostasitensfamilias_put';
  const PROPOSTASITENSFAMILIAS_DELETE = 'crm_propostasitensfamilias_delete';
  const FUNCOES_INDEX = 'gp_funcoes_index';
  const FUNCOES_CREATE = 'gp_funcoes_create';
  const FUNCOES_PUT =  'gp_funcoes_put';
  const FUNCOES_GET = 'gp_funcoes_get';
  const FUNCOES_DELETE = 'gp_funcoes_delete';
  const HISTORICOSPADRAO_INDEX = 'crm_historicospadrao_index';
  const HISTORICOSPADRAO_CREATE = 'crm_historicospadrao_create';
  const HISTORICOSPADRAO_PUT =  'crm_historicospadrao_put';
  const HISTORICOSPADRAO_GET = 'crm_historicospadrao_get';
  const HISTORICOSPADRAO_DELETE = 'crm_historicospadrao_delete';
  const MALOTES_INDEX = 'crm_malotes_index';
  const MALOTES_CREATE = 'crm_malotes_create';
  const MALOTES_PUT = 'crm_malotes_put';
  const MALOTES_ENVIAR = 'crm_malotes_malote_enviar';
  const MALOTES_CANCELARENVIO = 'crm_malotes_malotecancelaenvio';
  const MALOTES_APROVAR = 'crm_malotes_malote_aprova';
  const PENDENCIAS_INDEX = 'crm_atcspendencias_index';
  const PENDENCIAS_CREATE = 'crm_atcspendencias_create';
  const PENDENCIAS_PUT = 'crm_atcspendencias_put';
  const PENDENCIAS_DELETE = 'crm_atcspendencias_delete';
  const PENDENCIAS_MARCAR = 'crm_atcspendencias_marcar';

  const NEGOCIOS_INDEX = 'crm_negocios_index';
  const NEGOCIOS_GET = 'crm_negocios_get';
  const NEGOCIOS_CREATE = 'crm_negocios_create';
  const NEGOCIOS_PUT = 'crm_negocios_put';
  const NEGOCIOS_DELETE = 'crm_negocios_delete';
  const NEGOCIOS_QUALIFICARPRENEGOCIO = 'crm_negocios_pre_negocio_qualificar';
  const NEGOCIOS_DESQUALIFICARPRENEGOCIO = 'crm_negocios_pre_negocio_desqualificar';

  const FOLLOWUPSNEGOCIOS_INDEX = 'ns_followupsnegocios_index';
  const FOLLOWUPSNEGOCIOS_CREATE = 'ns_followupsnegocios_create';

  const SEGMENTOSATUACAO_INDEX = 'crm_segmentosatuacao_index';
  const SEGMENTOSATUACAO_GET = 'crm_segmentosatuacao_get';
  const SEGMENTOSATUACAO_CREATE = 'crm_segmentosatuacao_create';
  const SEGMENTOSATUACAO_PUT = 'crm_segmentosatuacao_put';
  const SEGMENTOSATUACAO_DELETE = 'crm_segmentosatuacao_delete';

  const SITUACOESPRENEGOCIOS_INDEX = 'crm_situacoesprenegocios_index';
  const SITUACOESPRENEGOCIOS_GET = 'crm_situacoesprenegocios_get';
  const SITUACOESPRENEGOCIOS_CREATE = 'crm_situacoesprenegocios_create';
  const SITUACOESPRENEGOCIOS_PUT = 'crm_situacoesprenegocios_put';
  const SITUACOESPRENEGOCIOS_DELETE = 'crm_situacoesprenegocios_delete';

  const PROMOCOESLEADS_INDEX = 'crm_promocoesleads_index';
  const PROMOCOESLEADS_GET = 'crm_promocoesleads_get';
  const PROMOCOESLEADS_CREATE = 'crm_promocoesleads_create';
  const PROMOCOESLEADS_PUT = 'crm_promocoesleads_put';
  const PROMOCOESLEADS_DELETE = 'crm_promocoesleads_delete';

  const LISTADAVEZVENDEDORES_INDEX = 'crm_listadavezvendedores_index';
  const LISTADAVEZVENDEDORES_GET = 'crm_listadavezvendedores_get';
  const LISTADAVEZVENDEDORES_CREATE = 'crm_listadavezvendedores_create';
  const LISTADAVEZVENDEDORES_PUT = 'crm_listadavezvendedores_put';
  const LISTADAVEZVENDEDORES_DELETE = 'crm_listadavezvendedores_delete';

  const LISTADAVEZCONFIGURACOES = 'crm_configuracoes_listadavezconfiguracoes';
  const RELATORIOS_PAINEL_MARKETING = 'crm_painel_marketing';

  const CIDADESINFORMACOESFUNERARIAS_INDEX = 'ns_cidadesinformacoesfunerarias_index';
  const CIDADESINFORMACOESFUNERARIAS_GET = 'ns_cidadesinformacoesfunerarias_get';
  const CIDADESINFORMACOESFUNERARIAS_CREATE = 'ns_cidadesinformacoesfunerarias_create';
  const CIDADESINFORMACOESFUNERARIAS_PUT = 'ns_cidadesinformacoesfunerarias_put';
  const CIDADESINFORMACOESFUNERARIAS_PRESTADORES = 'ns_cidadesinformacoesfunerarias_prestadores';

  const PRIORIDADES_INDEX = 'ns_prioridades_index';
  const PRIORIDADES_GET = 'ns_prioridades_get';
  const PRIORIDADES_CREATE = 'ns_prioridades_create';
  const PRIORIDADES_PUT = 'ns_prioridades_put';
  const PRIORIDADES_DELETE = 'ns_prioridades_delete';

  CONST CFGTAXASADM_GERENCIAR = 'crm_cfgtaxasadm_gerenciar';
  CONST CONTRATOSTAXASADM_GERENCIAR = 'crm_contratostaxasadm_gerenciar';

  CONST ATCSCONTASPAGAR_GERENCIAR = 'crm_atcscontasapagar_gerenciar';

}