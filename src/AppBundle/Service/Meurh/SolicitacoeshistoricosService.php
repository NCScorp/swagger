<?php

namespace AppBundle\Service\Meurh;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Meurh\SolicitacoeshistoricosService as ServiceParent;

/**
* SolicitacoeshistoricosService
*
*/
class SolicitacoeshistoricosService extends ServiceParent
{
    /**
     * @return string
     */
    public function getHistoricoSolicitacaoBySolicitacao($tenant, $solicitacao){

        return $this->getRepository()->getHistoricoSolicitacaoBySolicitacao($tenant, $solicitacao);

    }

    /**
     * @return array
     */
    public function findAnexos($solicitacaohistórico, $tenant){

        return $this->getRepository()->findAnexos($solicitacaohistórico, $tenant);

    }
} 