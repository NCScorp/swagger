<?php

namespace AppBundle\Repository\Ns;

use Nasajon\MDABundle\Repository\Ns\ConfiguracoesRepository as ParentRepository;

class ConfiguracoesRepository extends ParentRepository
{
 
    /**
    * @return array
    */
    public function getConfiguracaoByCampo($campo, $tenant, $estabelecimento) {
        $sql = "select conf.campo,
        conf.valor
        from ns.configuracoes as conf
        left join ns.estabelecimentos as est on conf.estabelecimento = est.estabelecimento
        where conf.tenant = :tenant and conf.campo = :campo and est.estabelecimento = :estabelecimento and grupo = 0";

        return $this->getConnection()->executeQuery($sql, [
            'estabelecimento' => $estabelecimento,
            'tenant' => $tenant,
            'campo' => $campo,
        ])->fetchAll();
    }        
}