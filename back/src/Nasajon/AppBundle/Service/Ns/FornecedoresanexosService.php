<?php

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Ns\FornecedoresanexosService as ParentService;

class FornecedoresanexosService extends ParentService
{
    protected $uploadFilesService;
    protected $adapter;
    protected $s3BucketPath;

    public function __construct(
        \Nasajon\MDABundle\Repository\Ns\FornecedoresanexosRepository $repository,
        $uploadFilesService,
        $adapter,
        $s3Path
    ) {
        parent::__construct($repository);
        $this->uploadFilesService = $uploadFilesService;
        $this->adapter = $adapter;
        $this->s3BucketPath = $s3Path;
    }

    /**
     * @param string  $fornecedor
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Ns\Fornecedoresanexos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($fornecedor, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Ns\Fornecedoresanexos $entity)
    {
        try {

            // Seto adapter de anexos no service de arquivos
            $this->uploadFilesService->adapter = $this->adapter;

            // Faço upload do anexo e preencho informações na entidade
            $urlArquivo = $this->uploadFilesService->upload($entity->getAnexo());
            $nomeArquivo = $entity->getAnexo()->getClientOriginalName();//"Cliente" . '.' . pathinfo($urlArquivo, PATHINFO_EXTENSION);

            $entity->setUrl($urlArquivo);
            $entity->setNomearquivo($nomeArquivo);
            $entity->setTipo($entity->getAnexo()->getMimeType());
            $entity->setTamanho($entity->getAnexo()->getSize());

            // Chamo insert do pai para seguir o fluxo normal
            $retorno = parent::insert($fornecedor, $tenant, $logged_user,  $entity);

            $retorno = $this->find($retorno['fornecedoranexo'], $fornecedor, $tenant);

            return $retorno;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Ns\Fornecedoresanexos $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, \Nasajon\MDABundle\Entity\Ns\Fornecedoresanexos $entity)
    {
        try {
            $this->getRepository()->begin();
            $response = $this->getRepository()->delete($tenant,  $entity);
            $this->getRepository()->commit();
            return $response;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }


    /**
     * @param string $id
     * @param mixed $fornecedor
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $fornecedor, $tenant)
    {
        // Chamo função do pai para buscar os dados do anexo
        $data = parent::find($id, $fornecedor, $tenant);

        // Seto adapter de anexos no service de arquivos
        $this->uploadFilesService->adapter = $this->adapter;

        // Preencho url correta do anexo
        if (trim($data['url']) !== '') {
            $novoNome = $data['nomearquivo'];
            $data['url'] = $this->uploadFilesService->getUrl($data['url'], $this->s3BucketPath, $novoNome);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function findAll($fornecedor, $tenant, Filter $filter = null)
    {
        // Chamo função do pai para buscar os dados do anexo
        $arrDados = parent::findAll($fornecedor, $tenant, $filter);

        // Seto adapter de anexos no service de arquivos
        $this->uploadFilesService->adapter = $this->adapter;

        // Para cada anexo, preencho a url correta
        foreach ($arrDados as &$anexo) {
            if (trim($anexo['url']) !== '') {
                $novoNome = $anexo['nomearquivo'];
                $anexo['url'] = $this->uploadFilesService->getUrl($anexo['url'], $this->s3BucketPath, $novoNome);
            }
        }
        // Retorno lista de anexos
        return $arrDados;
    }
}
