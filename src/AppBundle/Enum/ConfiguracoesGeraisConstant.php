<?php

namespace AppBundle\Enum;

class ConfiguracoesGeraisConstant{

    #-------------------------------------#
    #             Aplicações              #
    #-------------------------------------#
    const APLICACAO_GERAL = 0;
    const APLICACAO_PERSONA = 1;
    const APLICACAO_PERSONA_CLIENTE_WEB = 27;

    #-------------------------------------#
    #               Grupos                #
    #-------------------------------------#
    
    const GRUPO_GERAIS = 1;
    const GRUPO_PERSONALIZACAO_CLIENTE = 2;
    const GRUPO_GERAL_POR_EMPRESA = 3;
    const GRUPO_CONFIG_SAL_SOB_DEMANDA = 42;

    #-------------------------------------#
    #            Campos Gerais            #
    #-------------------------------------#
    const CAMPOSGERAIS_DIAS_PARA_CALCULO = 0;
    const CAMPOSGERAIS_DIA_FECHAMENTO_APONTAMENTO = 1;
    const CAMPOSGERAIS_ALERTA_AVISO = 2;
    const CAMPOSGERAIS_TIPOS_AVISOS_VISIVEIS = 3;
    const CAMPOSGERAIS_MENSAGEM_APONTAMENTO = 4;
    const CAMPOSGERAIS_RUBRICAS_APONTAMENTO = 5;
    const CAMPOSGERAIS_TITULO_MENSAGEM_BOAS_VINDAS = 6;
    const CAMPOSGERAIS_CORPO_MENSAGEM_BOAS_VINDAS = 7;
    const CAMPOSGERAIS_LIBERAR_CONSULTA_CONTRACHEQUE_FOLHA_CONSOLIDADA = 8;
    const CAMPOSGERAIS_PRAZO_MINIMO_SOLICITACAO_RESCISAO = 11;
    const CAMPOSGERAIS_PRAZO_MINIMO_SOLICITACAO_ADMISSAO = 12;
    const CAMPOSGERAIS_COMUNICADOS_HOME = 13;
    const CAMPOSGERAIS_PRAZOS_AVISOS = 14;
    const CAMPOSGERAIS_MES_FECHAMENTO = 15;
    const CAMPOSGERAIS_MARCAR_FERIAS_ANTES_PERIODO = 134;

    #-------------------------------------#
    #  Campos Configuração Cálculo        #
    #-------------------------------------#
    const CALCULO_FORMATO_HHMM = 3;

    #-------------------------------------#
    #  Campos Configuração Pramex        #
    #-------------------------------------#
    const CAMPO_PERSONALIZACAO = 33; 

    #-------------------------------------#
    #  Campos do salario sob demanda      #
    #-------------------------------------#
    const CAMPOSALSOBDEMANDA_ATIVAR = 0;

    /********************* constante auxilar temporária*******************/

    const TENANT = 0;

    /****************************************** */

    /*************campos das configurações gerais do Persona SQL */

    const CAMPO_PERGUNTARGRAVARCALCULOSPOSRELATORIO13 = 1;
    const CAMPO_PERGUNTARGRAVARCALCULOSPOSRELATORIOFERIAS= 2;
    const CAMPO_PERGUNTARGRAVARCALCULOSPOSRELATORIORESCISAO = 3;
    const CAMPO_TELADEMOVIMENTOSAPOSCADASTRARTRABALHADOR = 4;
    const CAMPO_VISIBILIDADECAMPOSALARIO = 5;
    const CAMPO_VALORGENERICO = 6;
    const CAMPO_VALORMAXIMODOC = 7;
    const CAMPO_DECISAOTEDOUDOC = 8;
    const CAMPO_USAPREFIXOPAGAMENTODIFERENCA = 9;
    const CAMPO_PREFIXOPAGAMENTODIFERENCA = 10;
    const CAMPO_ALTERACOESPARAHOMOLOGACAO = 11;
    const CAMPO_PERCENTUALMULTADIARIA = 12;
    const CAMPO_PERCENTUALLIMITEMULTA = 13;
    const CAMPO_LIGALOGCALCULO = 14;
    const CAMPO_PEDICONFIRMCADAVFERIASMSMPERIODO = 15;
    const CAMPO_VRTRANSPCODLINHAPERSONALIZADO = 16;
    const CAMPO_VRTRANSPUSARDADOSDOBUREAU = 17;
    const CAMPO_VRTRANSPDADOSADICIONAIS = 18;

    const CAMPO_TICKETLISTAASSINATURAS = 19;
    const CAMPO_TICKETTRANSPPACOTESEPARADO = 20;
    const CAMPO_TICKETPRECOPORPONTOTICKET = 21;
    const CAMPO_TICKETUSARDADOSDOBUREAU = 22;
    const CAMPO_TICKETDIRETORIO = 23;
    const CAMPO_TICKETCODIGOLINHAPERSON = 24;

    const CAMPO_EXIBIRNOTIFICACOESPERSONACLIENTE = 25;
    const CAMPO_TEMPONOTIFICACOESPERSONACLIENTE = 26;
    const CAMPO_HABILITAMANAD = 27;


    const TIPODECISAOTDCFG_PELOSISTEMA = 1;
    const TIPODECISAOTDCFG_SEMPRETED = 2;


    const GRUPO_CONFIGURACAO_CALCULO = 0;
    const GRUPO_CONFIGURACAO_GERAL = 25;
    const PAGAMENTO_PROXIMO_MES = 1;
    const PAGAMENTO_DIA_FOLHA = 2;
    const PAGAMENTO_DIA_ADIANTAMENTO = 15;
}