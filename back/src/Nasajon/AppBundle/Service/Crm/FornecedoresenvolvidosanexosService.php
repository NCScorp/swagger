<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Gaufrette\Adapter;
use Nasajon\MDABundle\Service\Crm\FornecedoresenvolvidosanexosService as ParentService;

/**
* FornecedoresenvolvidosanexosService
*
*/
class FornecedoresenvolvidosanexosService extends ParentService
{
    protected $uploadFilesService;
    /**
     * 
     * @var Adapter $adapter 
     */
    protected $adapter;
    protected $s3BucketPath;

    public function __construct(
        \Nasajon\MDABundle\Repository\Crm\FornecedoresenvolvidosanexosRepository $repository,
        $uploadFilesService,
        $adapter,
        $s3Path
    ){ 
        parent::__construct($repository);
        $this->uploadFilesService = $uploadFilesService;
        $this->adapter = $adapter;
        $this->s3BucketPath = $s3Path;
    }

    /**
     * @param string  $fornecedorenvolvido
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidosanexos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($fornecedorenvolvido, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidosanexos $entity){
        try {
            // Seto adapter de anexos no service de arquivos
            $this->uploadFilesService->adapter = $this->adapter;

            $filePrename = $entity->getNomearquivo() . '_';

            // Faço upload do anexo e preencho informações na entidade
            $urlArquivo = $this->uploadFilesService->upload($entity->getAnexo(), $filePrename);
            
            $entity->setUrl($urlArquivo);
            $entity->setNomearquivo($urlArquivo);
            $entity->setTipo($entity->getAnexo()->getMimeType());
            $entity->setTamanho($entity->getAnexo()->getSize());

            // Chamo insert do pai para seguir o fluxo normal
            $retorno = parent::insert($fornecedorenvolvido, $tenant, $logged_user, $entity);

            return $retorno;
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * @param string $id
     * @param mixed $fornecedorenvolvido
     * @param mixed $tenant
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $fornecedorenvolvido, $tenant){
        // Chamo função do pai para buscar os dados do anexo
        $data = parent::find($id, $fornecedorenvolvido, $tenant);

        // Seto adapter de anexos no service de arquivos
        $this->uploadFilesService->adapter = $this->adapter;

        // Preencho url correta do anexo
        if (trim($data['url']) !== ''){
            $novoNome = $anexo['nomearquivo'];
            $data['url'] = $this->uploadFilesService->getUrl($data['url'], $this->s3BucketPath, $novoNome);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function findAll($fornecedorenvolvido,$tenant, Filter $filter = null){
        // Chamo função do pai para buscar os dados do anexo
        $arrDados = parent::findAll($fornecedorenvolvido, $tenant, $filter);

        // Seto adapter de anexos no service de arquivos
        $this->uploadFilesService->adapter = $this->adapter;
        
        // Para cada anexo, preencho a url correta
        foreach ($arrDados as &$anexo) {
            if (trim($anexo['url']) !== ''){
                $novoNome = $anexo['nomearquivo'];
                $anexo['url'] = $this->uploadFilesService->getUrl($anexo['url'], $this->s3BucketPath, $novoNome);
            }
        }

        // Retorno lista de anexos
        return $arrDados;            
    }
}