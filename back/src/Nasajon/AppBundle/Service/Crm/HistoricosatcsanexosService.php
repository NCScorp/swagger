<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Entity\Crm\Historicosatcsanexos;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Service\Crm\HistoricosatcsanexosService as ParentService;

class HistoricosatcsanexosService extends ParentService
{
    /**
     * @var \Nasajon\MDABundle\Repository\Crm\HistoricosatcsanexosRepository
     */
    protected $repository;

    public function __construct(
        $repository,
        $uploadFilesService,
        $adapter,
        $s3BucketPath
    ) {
        $this->repository = $repository;
        $this->uploadFilesService = $uploadFilesService;
        $this->adapter = $adapter;
        $this->s3BucketPath = $s3BucketPath;
    }

    
    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Historicosatcsanexos $entity
     * @return string
     * @throws \Exception
     */
    public function insert($historicoatc, $tenant, $logged_user, \Nasajon\MDABundle\Entity\Crm\Historicosatcsanexos $entity)
    {
        try {
            $this->getRepository()->begin();
            // Seto adapter de anexos no service de arquivos
            $this->uploadFilesService->adapter = $this->adapter;

            // Faço upload do anexo e preencho informações na entidade
            $urlArquivo = $this->uploadFilesService->upload($entity->getAnexo());
            $nomeArquivo = $entity->getAnexo()->getClientOriginalName();

            $entity->setUrl($urlArquivo);
            $entity->setArquivo($nomeArquivo);
            $entity->setTipo($entity->getAnexo()->getMimeType());
            $entity->setTamanho($entity->getAnexo()->getSize());

            // Chamo insert do pai para seguir o fluxo normal
            $retorno = parent::insert($historicoatc, $tenant, $logged_user, $entity);

            $retorno = $this->find($retorno['documentoged'], $historicoatc, $tenant);
            $this->getRepository()->commit();

            return $retorno;
        } catch (\Exception $e) {
            $this->getRepository()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string  $tenant
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Historicosatcsanexos $entity
     * @return string
     * @throws \Exception
     */
    public function insertRaw($historicoatc, $tenant, $logged_user, $dadosArquivo, $nomeArquivo, $extensaoArquivo)
    {
        try {
            // Seto adapter de anexos no service de arquivos
            $this->uploadFilesService->adapter = $this->adapter;

            // Faço upload do anexo e preencho informações na entidade
            $urlArquivo = $this->uploadFilesService->uploadRawFile($dadosArquivo, $extensaoArquivo);

            $entity = new Historicosatcsanexos();
            $entity->setUrl($urlArquivo);
            $entity->setArquivo($nomeArquivo);
            // $entity->setTipo($entity->getAnexo()->getMimeType());
            // $entity->setTamanho($entity->getAnexo()->getSize());

            // Chamo insert do pai para seguir o fluxo normal
            $retorno = parent::insert($historicoatc, $tenant, $logged_user, $entity);

            $retorno = $this->find($retorno['documentoged'], $historicoatc, $tenant);
            $this->getRepository()->commit();

            return $retorno;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $id
     * @param mixed $tenant
          
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id , $historicoatc, $tenant)
    {
        // Chamo função do pai para buscar os dados do anexo
        $data = parent::find($id , $historicoatc, $tenant);

        // Seto adapter de anexos no service de arquivos
        $this->uploadFilesService->adapter = $this->adapter;

        // Preencho url correta do anexo
        if (trim($data['url']) !== '') {
            $novoNome = $data['arquivo'];
            $data['url'] = $this->uploadFilesService->getUrl($data['url'], $this->s3BucketPath, $novoNome);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function findAll($historicoatc, $tenant, Filter $filter = null)
    {
        // Chamo função do pai para buscar os dados do anexo
        $arrDados = parent::findAll($historicoatc, $tenant, $filter);

        // Seto adapter de anexos no service de arquivos
        $this->uploadFilesService->adapter = $this->adapter;

        // Para cada anexo, preencho a url correta
        foreach ($arrDados as &$anexo) {
            if (trim($anexo['url']) !== '') {
                $novoNome = $anexo['arquivo'];
                $anexo['url'] = $this->uploadFilesService->getUrl($anexo['url'], $this->s3BucketPath, $novoNome);
            }
        }

        // Retorno lista de anexos
        return $arrDados;
    }

}
