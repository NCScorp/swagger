<?php

namespace Nasajon\AppBundle\Service\Ns;

use GuzzleHttp\Psr7\UploadedFile;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Ns\ClientesanexosService as ParentService;

class ClientesanexosService extends ParentService
{
    protected $uploadFilesService;
    /**
     * @var Adapter $adapter 
     */
    protected $adapter;
    protected $s3BucketPath;

    public function __construct(
        \Nasajon\MDABundle\Repository\Ns\ClientesanexosRepository $repository,
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
     * @param string  $cliente
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Ns\Clientesanexos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($cliente, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Ns\Clientesanexos $entity)
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
            $retorno = parent::insert($cliente, $tenant, $logged_user, $entity);

            $retorno = $this->find($retorno['clienteanexo'], $cliente, $tenant);

            return $retorno;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param \Nasajon\MDABundle\Entity\Ns\Clientesanexos $entity
     * @return string
     * @throws \Exception
     */
    public function delete($tenant, \Nasajon\MDABundle\Entity\Ns\Clientesanexos $entity)
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
     * @param mixed $cliente
     * @param mixed $tenant
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $cliente, $tenant)
    {
        // Chamo função do pai para buscar os dados do anexo
        $data = parent::find($id, $cliente, $tenant);

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
    public function findAll($cliente, $tenant, Filter $filter = null)
    {
        // Chamo função do pai para buscar os dados do anexo
        $arrDados = parent::findAll($cliente, $tenant, $filter);

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
