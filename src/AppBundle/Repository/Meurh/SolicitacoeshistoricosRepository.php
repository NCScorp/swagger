<?php

namespace AppBundle\Repository\Meurh;

use Nasajon\MDABundle\Repository\Meurh\SolicitacoeshistoricosRepository as ParentRepository;

/**
* SolicitacoeshistoricosRepository
*
*/
class SolicitacoeshistoricosRepository extends ParentRepository
{
    /**
     * @return string
     */
    public function getHistoricoSolicitacaoBySolicitacao($tenant, $solicitacao){
        $sql =
           "SELECT
                solicitacaohistorico
            FROM
                meurh.solicitacoeshistoricos _t1
            WHERE
                _t1.tenant = :tenant and _t1.solicitacao = :solicitacao
            ORDER BY
                created_at DESC";

        return $this->getConnection()->executeQuery($sql, [
            'solicitacao' => $solicitacao,
            'tenant' => $tenant
        ])->fetch();
    }

    /**
     * @return array
     */
    public function findAnexos($solicitacaohistorico, $tenant){
        $sql =
           "SELECT 
             solicitacaohistorico,
             tenant,
             anexos
           FROM 
             meurh.solicitacoeshistoricos t0_
           WHERE 
             t0_.solicitacaohistorico = :id AND t0_.tenant = :tenant";

        return $this->getConnection()->executeQuery($sql, [
            'id' => $solicitacaohistorico,
            'tenant' => $tenant
        ])->fetch();
    }

}