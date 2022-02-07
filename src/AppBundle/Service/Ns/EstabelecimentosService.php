<?php

namespace AppBundle\Service\Ns;

use Nasajon\MDABundle\Service\Ns\EstabelecimentosService as ParentService;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;

class EstabelecimentosService extends ParentService
{    
    /**
     * @return array
     */
    public function findAll($tenant, Filter $filter = null, $estabelecimentos = null){

        if (is_null($filter)) {
            $filter = new Filter();
        }

        if(!is_null($estabelecimentos)) {
            $filter->addToFilterExpression(new FilterExpression('estabelecimento', 'in', $estabelecimentos));
        }

        $estabelecimentos = parent::findAll($tenant, $filter);

        return $estabelecimentos;
    }

    /**
     * @return array
     */
    public function findEstabelecimentosByEmailTrabalhador($tenant, $contanasajon, $estabelecimentos = null)
    {
        $data = $this->getRepository()->findEstabelecimentosByEmailTrabalhador($tenant, $contanasajon, $estabelecimentos);

        return $data;
    }

    public function findEstabelecimentoComGrupo($tenant, $estabelecimento) {
        $data = $this->getRepository()->findEstabelecimentoComGrupo($tenant, $estabelecimento);

        return $data;
    }


    public function getEmpresaByEstabelecimento($tenant, $estabelecimento) {
        $data = $this->getRepository()->findEmpresaByEstabelecimento($tenant, $estabelecimento);

        return $data;
    }
}
