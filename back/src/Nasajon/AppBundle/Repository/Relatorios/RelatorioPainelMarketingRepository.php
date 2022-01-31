<?php

namespace Nasajon\AppBundle\Repository\Relatorios;

use Nasajon\MDABundle\Repository\AbstractRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter as Filter;

class RelatorioPainelMarketingRepository extends ParentRepository
{
    /**
     * Busca todos os negócios dentro do período $datainicio e $datafinal.
     * Filtro por campanha é opcional.
     */
    public function getNegocios($tenant, $id_grupoempresarial, $datainicio, $datafinal, $campanha = null){
        // Monto consulta para buscar negócios
        $sql = 
            "SELECT 
                N.DOCUMENTO AS NEGOCIO_ID,
                N.ID_CODIGODEPROMOCAO AS CAMPANHA_ID, 
                PL.NOME AS CAMPANHA_NOME,
                N.ID_MIDIADEORIGEM AS MIDIA_ID, 
                MO.DESCRICAO AS MIDIA_NOME,
                N.ID_TIPOACIONAMENTO AS TIPOACIONAMENTO_ID,
                TA.DESCRICAO AS TIPOACIONAMENTO_NOME,
                N.ID_ESTABELECIMENTO AS ESTABELECIMENTO_ID, 
                EST.DESCRICAO AS ESTABELECIMENTO_NOME,
                N.ID_OPERACAO AS AREA_ID,
                NO.DESCRICAO AS AREA_NOME,
                N.CLIENTE_CAPTADOR AS CAPTADOR_ID,
                CAP.NOME AS CAPTADOR_NOME,
                N.CLIENTE_SEGMENTODEATUACAO AS SEGMENTOATUACAO_ID, 
                SA.DESCRICAO AS SEGMENTOATUACAO_NOME,
                N.CLIENTE_RECEITAANUAL AS FATURAMENTO_NOME,
                N.EHCLIENTE AS JA_E_CLIENTE, 
                TO_CHAR(N.CREATED_AT, 'YYYYMM') AS ANOMES_CRIACAO, 
                TO_CHAR(N.CREATED_AT_QUALIFICACAO_PN, 'YYYYMM') AS ANOMES_QUALIFICACAO, 
                N.PRENEGOCIO AS PRENEGOCIO, 
                N.TIPOQUALIFICACAO_PN AS TIPOQUALIFICACAO, 
                N.ID_MOTIVODESQUALIFICACAO_PN AS MOTIVO_ID,
                MDP.DESCRICAO AS MOTIVO_NOME
            FROM CRM.NEGOCIOS N
                LEFT JOIN CRM.PROMOCOESLEADS PL ON PL.PROMOCAOLEAD = N.ID_CODIGODEPROMOCAO AND PL.TENANT = N.TENANT   
                LEFT JOIN CRM.MIDIASORIGEM MO  ON MO.MIDIAORIGEM = N.ID_MIDIADEORIGEM AND MO.TENANT = N.TENANT   
                LEFT JOIN CRM.TIPOSACIONAMENTOS TA ON TA.TIPOSACIONAMENTO = N.ID_TIPOACIONAMENTO AND TA.TENANT = N.TENANT   
                LEFT JOIN NS.ESTABELECIMENTOS EST ON EST.ESTABELECIMENTO = N.ID_ESTABELECIMENTO AND EST.TENANT = N.TENANT   
                LEFT JOIN CRM.NEGOCIOSOPERACOES NO ON NO.PROPOSTA_OPERACAO = N.ID_OPERACAO AND NO.TENANT = N.TENANT        
                LEFT JOIN NS.PESSOAS CAP ON CAP.ID = N.CLIENTE_CAPTADOR AND CAP.TENANT = N.TENANT AND COALESCE(CAP.VENDEDORATIVADO, 0) = 1
                LEFT JOIN CRM.SEGMENTOSATUACAO SA ON SA.SEGMENTOATUACAO = N.CLIENTE_SEGMENTODEATUACAO AND SA.TENANT = N.TENANT   
                LEFT JOIN CRM.MOTIVOSDESQUALIFICACOESPRENEGOCIOS MDP ON MDP.MOTIVODESQUALIFICACAOPRENEGOCIO = N.ID_MOTIVODESQUALIFICACAO_PN AND MDP.TENANT = N.TENANT
            WHERE N.TENANT = :TENANT
              AND N.ID_GRUPOEMPRESARIAL = :GRUPOEMPRESARIAL
              AND N.CREATED_AT::DATE BETWEEN :DATAINICIO AND :DATAFIM
        ";

        // Faço bind dos parâmetros da consulta
        $bindQuery = [
            'TENANT' => $tenant,
            'GRUPOEMPRESARIAL' => $id_grupoempresarial,
            'DATAINICIO' => $datainicio,
            'DATAFIM' => $datafinal
        ];

        // Se passou a campanha, adiciono filtro a consulta e faço bind
        if ($campanha != null) {
            $sql .= ' AND N.ID_CODIGODEPROMOCAO = :CAMPANHA';
            $bindQuery['CAMPANHA'] = $campanha;
        }

        // Executo SQL
        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        // Retorno lista de dados
        return $data;
    }

    /**
     * Busca todos os contatos dos negócios dentro do período $datainicio e $datafinal.
     * Filtro por campanha é opcional.
     */
    public function getNegociosContatos($tenant, $id_grupoempresarial, $datainicio, $datafinal, $campanha = null){
        // Monto consulta para buscar contatos dos negócios
        $sql = 
            "SELECT DISTINCT
                NC.ID_NEGOCIO AS NEGOCIO_ID,
                NC.CARGO AS CONTATO_CARGO
            FROM CRM.NEGOCIOSCONTATOS NC  
                JOIN CRM.NEGOCIOS N ON N.DOCUMENTO = NC.ID_NEGOCIO AND N.TENANT = NC.TENANT
            WHERE NC.TENANT = :TENANT
              AND NC.ID_GRUPOEMPRESARIAL = :GRUPOEMPRESARIAL
              AND N.CREATED_AT::DATE BETWEEN :DATAINICIO AND :DATAFIM
        ";

        // Faço bind dos parâmetros da consulta
        $bindQuery = [
            'TENANT' => $tenant,
            'GRUPOEMPRESARIAL' => $id_grupoempresarial,
            'DATAINICIO' => $datainicio,
            'DATAFIM' => $datafinal
        ];

        // Se passou a campanha, adiciono filtro a consulta e faço bind
        if ($campanha != null) {
            $sql .= ' AND N.ID_CODIGODEPROMOCAO = :CAMPANHA';
            $bindQuery['CAMPANHA'] = $campanha;
        }

        // Executo SQL
        $data = $this->getConnection()->executeQuery($sql, $bindQuery)->fetchAll();

        // Retorno lista de dados
        return $data;
    }
}
