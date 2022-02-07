<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;

class TenantsService 
{
    /**
    * @var \AppBundle\Repository\Ns\TenantsRepository
    */
    protected $repository;
    
    /**
     * @return \AppBundle\Repository\Ns\TenantsRepository
     */
    public function getRepository(){
        return $this->repository;
    }
    
    public function __construct(\AppBundle\Repository\Ns\TenantsRepository $repository ){
        $this->repository = $repository;
    }

    public function findOneByCodigo($codigo){
        return $this->repository->findOneByCodigo($codigo);
    }


    public function getRetornoProfile($codigo){
        $tenant = [];
        $tenant = $this->repository->getTenantProfile($codigo);
        $tenant['gruposempresariais'] = $this->repository->getGrupoProfile($tenant['id']);
        foreach ($tenant['gruposempresariais'] as $cod => $grupo) {
            $tenant['gruposempresariais'][$cod]['empresas'] = $this->repository->getEmpresaProfile($grupo['id']);
            foreach ($tenant['gruposempresariais'][$cod]['empresas'] as $codempresa => $empresa) {
                $tenant['gruposempresariais'][$cod]['empresas'][$codempresa]['estabelecimentos'] = $this->repository->getEstabelecimentoProfile($empresa['id']);
            }
        }
        
        return $tenant;
    }



}