<?php
namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Crm\AtcsfollowupsanexosService as ParentService;

/**
* AtcsfollowupsanexosService
*
*/
class AtcsfollowupsanexosService extends ParentService
{
    // Path utilizado para fazer upload dos anexos
    protected $documentosUploadPath;
    // Service de upload de arquivos
    protected $uploadFilesService;
    
    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\AtcsfollowupsanexosRepository $repository,
        $documentosUploadPath,
        $uploadFilesService
    ){
        parent::__construct($repository);
        $this->documentosUploadPath = $documentosUploadPath;
        $this->uploadFilesService = $uploadFilesService;
    }
 
    /**
     * Sobrescrito para trazer os anexos com permissão de visualização de 1h
     * @return array
     */
    public function findAll($atcfollowup,$tenant, Filter $filter = null){

        $retorno = parent::findAll($atcfollowup, $tenant, $filter);
        if (is_array($retorno)){
            foreach ($retorno as &$anexo) {
                $urlNova = $this->uploadFilesService->getUrl($anexo['nomearquivo'], $this->documentosUploadPath);
                $anexo['url'] = $urlNova;
            }
        }
        
        return $retorno;
    }

    /**
     * Sobrescrito para trazer os anexo com permissão de visualização de 1h
     * @param string  $negociofollowup
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Atcsfollowupsanexos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($negociofollowup,$tenant,$logged_user, \Nasajon\MDABundle\Entity\Crm\Atcsfollowupsanexos $entity){
        $anexo = parent::insert($negociofollowup, $tenant, $logged_user, $entity);

        $urlNova = $this->uploadFilesService->getUrl($anexo['nomearquivo'], $this->documentosUploadPath);
        $anexo['url'] = $urlNova;
        
        return $anexo;
    }
}