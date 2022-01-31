<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace Nasajon\AppBundle\Service\Ns\Pessoas;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\Pessoas\LogosService as ParentService;

/**
* LogosService
*
*/
class LogosService extends ParentService
{
    /**
    * @var \Nasajon\MDABundle\Repository\Ns\Pessoas\LogosRepository
    */
    protected $repository;

    protected $uploadFilesService;
    
    /**
     * @return \Nasajon\MDABundle\Repository\Ns\Pessoas\LogosRepository
     */
    public function getRepository(){
        return $this->repository;
    }
    
    public function __construct(
        \Nasajon\MDABundle\Repository\Ns\Pessoas\LogosRepository $repository,
        $uploadFilesService
    ){     
        parent::__construct($repository);
        $this->uploadFilesService = $uploadFilesService;
    }
    
    /**
    * @param string  $idpessoa
    * @param string  $id_grupoempresarial
    * @param string  $tenant
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Ns\Pessoas\Logos $entity
    * @return string
    * @throws \Exception
    */
    public function insert($idpessoa,$id_grupoempresarial,$tenant,$logged_user, \Nasajon\MDABundle\Entity\Ns\Pessoas\Logos $entity){
        try {
            // Faço upload da logo e preencho informações na entidade
            $nomeArquivo = $this->uploadFilesService->upload($entity->getLogo());
            $entity->setPathLogo($nomeArquivo);

            // Chamo insert do pai para seguir o fluxo normals
            $retorno = parent::insert($idpessoa, $id_grupoempresarial, $tenant, $logged_user, $entity);

            // Insiro url da logo do fornecedor
            if (is_array($retorno) && isset($retorno['pathlogo'])){
                $retorno['pathlogo'] = $this->uploadFilesService->getUrl($retorno['pathlogo']);
            }

            return $retorno;
        }catch(\Exception $e){
            throw $e;
        }
    }
    
    
}