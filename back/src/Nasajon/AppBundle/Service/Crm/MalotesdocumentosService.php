<?php

namespace Nasajon\AppBundle\Service\Crm;

use Gaufrette\Adapter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\MalotesdocumentosService as ParentService;
use Aws\S3\S3Client;  
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Sobrescrita para manipulação dos documentos do malote com o adapter do Gaufrette.
 */
class MalotesdocumentosService extends ParentService
{
    /**
     * 
     * @var Adapter $adapter 
     */
    private $adapter;

    /**
     * @var S3Client
     */
    private $s3Client;

    private $s3BucketName;

    private $s3BucketPath;

    /**
     * Service de upload de arquivos
     */
    private $uploadFilesService;

    /**
     * Sobrescrito para incluir chamada ao s3
     * @param \Nasajon\AppBundle\Repository\MalotesdocumentosRepository $repository
     * @param Adapter $adapter
     * @param ParameterBag $fixedAttributes
     */
    public function __construct(
        \Nasajon\AppBundle\Repository\Crm\MalotesdocumentosRepository $repository,
        Adapter $adapter,
        S3Client $s3Client,
        $s3Bucket,
        $s3Path,
        $uploadFilesService,
        $crmAtcsDocumentosService,
        $crmAtcstiposdocumentosrequisitantesService
    ){
        parent::__construct($repository);

        $this->adapter = $adapter;
        $this->s3Client = $s3Client;
        $this->s3BucketName = $s3Bucket;
        $this->s3BucketPath = $s3Path;
        $this->uploadFilesService = $uploadFilesService;
        $this->crmAtcsDocumentosService = $crmAtcsDocumentosService;
        $this->crmAtcstiposdocumentosrequisitantesService = $crmAtcstiposdocumentosrequisitantesService;
    }

    /**
     * Retorna os Documentos de um Malote
     * @param string $tenant
     * @param Filter $filter
     * @return array
     */
    public function findAll($tenant, $malote, $id_grupoempresarial, Filter $filter = null)
    {
        $arrDados = parent::findAll($tenant, $malote, $id_grupoempresarial, $filter);

        // Adiciono url do documento
        $this->uploadFilesService->adapter = $this->adapter;

        foreach ($arrDados as &$malotedocumento) {
            $novoNome = $malotedocumento['documento']['negocio_nome'].' - ' . 
                substr($malotedocumento['documento']['created_at'], 0, strpos($malotedocumento['documento']['created_at'], '.')) .' - ' . 
                $malotedocumento['documento']['tipodocumento_nome'] . '.' . pathinfo($malotedocumento['documento']['url'], PATHINFO_EXTENSION);

            $malotedocumento['documento']['url'] = $this->uploadFilesService->getUrl(
                $malotedocumento['documento']['url'], $this->s3BucketPath, $novoNome
            );

            //achar atcsdocumentos
            $atcDocumento = $this->crmAtcsDocumentosService->find($malotedocumento['documento']['negociodocumento'], $tenant, $id_grupoempresarial);

            $filter = new Filter();
            $expressions = [];
            $expressions[] = new FilterExpression('tipodocumento.tipodocumento', 'eq', $atcDocumento['tipodocumento']['tipodocumento']);
            $filter->setFilterExpression($expressions);

            $atcsTiposDocumentosRequisitantes = $this->crmAtcstiposdocumentosrequisitantesService
                ->findAll($tenant, $atcDocumento['negocio']['negocio'], $id_grupoempresarial, $filter);
            if(!empty($atcsTiposDocumentosRequisitantes[0])){
                $malotedocumento['tiposCopias']['copiasimples'] = $atcsTiposDocumentosRequisitantes[0]['copiasimples'];
                $malotedocumento['tiposCopias']['copiaautenticada'] = $atcsTiposDocumentosRequisitantes[0]['copiaautenticada'];
                $malotedocumento['tiposCopias']['original'] = $atcsTiposDocumentosRequisitantes[0]['original'];
            }
        }

        return $arrDados;
    }
}