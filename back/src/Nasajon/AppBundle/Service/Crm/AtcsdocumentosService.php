<?php

namespace Nasajon\AppBundle\Service\Crm;

use Gaufrette\Adapter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Crm\AtcsdocumentosService as ParentService;
use Aws\S3\S3Client;  
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use LogicException;

/**
 * Sobrescrita para manipulação dos documentos do negócio com o adapter do Gaufrette.
 */
class AtcsdocumentosService extends ParentService
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

  public $atcstiposdocumentosrequisitantesService;

  protected $uploadFilesService;

  public $atcscontasapagarService;

    /**
     * Sobrescrito para incluir chamada ao camposcustomizados service
     * @param \Nasajon\AppBundle\Repository\Crm\AtcsdocumentosRepository $repository
     * @param Adapter $adapter
     * @param ParameterBag $fixedAttributes
     */
    public function __construct(
        \Nasajon\AppBundle\Repository\Crm\AtcsdocumentosRepository $repository,
        Adapter $adapter,
        S3Client $s3Client,
        $s3Bucket,
        $s3Path,
        $uploadFilesService,
        $atcscontasapagarService
    ) {
        parent::__construct($repository);

        $this->adapter = $adapter;
        $this->s3Client = $s3Client;
        $this->s3BucketName = $s3Bucket;
        $this->s3BucketPath = $s3Path;
        $this->uploadFilesService = $uploadFilesService;
        $this->atcscontasapagarService = $atcscontasapagarService;
    }

    /**
     * @param string $id
     * @param mixed $tenant
     * @param mixed $id_grupoempresarial
     * 
     * @return array
     * @throw \Doctrine\ORM\NoResultException
     */
    public function find($id, $tenant, $id_grupoempresarial){
        $entidade = parent::find($id, $tenant, $id_grupoempresarial);

        // Adicionando a url do documento, com a extensão do arquivo
        $nomeArquivo = $entidade['negocio']['nome'].' - ' . 
        substr($entidade['created_at'], 0, strpos($entidade['created_at'], '.')) .' - ' . 
        $entidade['tipodocumento']['nome'] . '.' . pathinfo($entidade['url'], PATHINFO_EXTENSION);

        $command = $this->s3Client->getCommand('GetObject', [ 
            'Bucket' => $this->s3BucketName, 
            'Key' => $this->s3BucketPath.'/'.$entidade['url'],
            'ResponseContentDisposition' => 'attachment; filename='.$nomeArquivo 
        ]);
                                        
        $presignedRequest = $this->s3Client->createPresignedRequest($command, "+1 hour");
        $presignedUrl = (string) $presignedRequest->getUri();

        $entidade['ext'] = pathinfo($entidade['url'], PATHINFO_EXTENSION);
        $entidade['url'] = $presignedUrl;

        return $entidade;
    }

  /**
   * @return array
   */
  public function findAll($tenant, $id_grupoempresarial, Filter $filter = null)
  {    
    $this->getRepository()->validateOffset($filter);

    $entidades = $this->getRepository()->findAll($tenant, $id_grupoempresarial, $filter);

    $entidades = array_map(function($entidade) {

      // Adicionando a url do documento, com a extensão do arquivo
      $nomeArquivo = $entidade['negocio']['nome'].' - ' . 
                  substr($entidade['created_at'], 0, strpos($entidade['created_at'], '.')) .' - ' . 
                  $entidade['tipodocumento']['nome'] . '.' . pathinfo($entidade['url'], PATHINFO_EXTENSION);

      $command = $this->s3Client->getCommand('GetObject', 
                                              [ 'Bucket' => $this->s3BucketName, 
                                                'Key' => $this->s3BucketPath.'/'.$entidade['url'],
                                                'ResponseContentDisposition' => 'attachment; filename='.$nomeArquivo ]);
                                                
      $presignedRequest = $this->s3Client->createPresignedRequest($command, "+1 hour");
      $presignedUrl = (string) $presignedRequest->getUri();

      $entidade['ext'] = pathinfo($entidade['url'], PATHINFO_EXTENSION);
      $entidade['url'] = $presignedUrl;

      return $entidade;
    }, $entidades);

    return $entidades;
  }

    /**
    * @param string  $tenant
    * @param string  $logged_user
    * @param \Nasajon\MDABundle\Entity\Crm\Atcsdocumentos $entity
    * @return string
    * @throws \Exception
    */
    public function insert($tenant, $logged_user, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcsdocumentos $entity){
        try {
            $filter = new Filter();
            $expressions = [];
            $expressions[] = new FilterExpression('tipodocumento.tipodocumento', 'eq', $entity->getTipodocumento()->getTipodocumento());
            $filter->setFilterExpression($expressions);
            $documentosEsperados = $this->atcstiposdocumentosrequisitantesService->findAll($tenant, $entity->getNegocio()->getNegocio(), $id_grupoempresarial, $filter);
            
            if (count($documentosEsperados) == 0) {
                throw new \Exception('O atendimento comercial não requer esse tipo de documento.');
            }

            try {
                $this->getRepository()->begin();

                // Insiro atc documento
                $novoAtcDocumento = $this->getRepository()->insert($tenant, $logged_user, $id_grupoempresarial, $entity);

                // Se foram passados orçamentos, atualizo contas a pagar com dados do documento
                $arrOrcamentos = $entity->getOrcamentos()->toArray();
                if (count($arrOrcamentos) > 0 && $entity->getNumerodocumento() != null) {
                    // Busco contas a pagar dos orçamentos passados
                    $filter = new Filter();
                    $arrFilterExpression = [];
                    foreach ($arrOrcamentos as $orcamento) {
                        $arrFilterExpression[] = new FilterExpression('orcamento', 'eq', $orcamento->getOrcamento());   
                    }
                    $filter->setFilterExpression($arrFilterExpression);
                    $arrContaspagar = $this->atcscontasapagarService->findAll($tenant, $id_grupoempresarial, $entity->getNegocio()->getNegocio(), $filter);
        
                    // Atualizo contas a pagar para preencher informações do documento
                    for ($i=0; $i < count($arrContaspagar); $i++) { 
                        $contaPagarObj = $this->atcscontasapagarService->fillEntity($arrContaspagar[$i]);
                     
                        // Atualizo dados do documento null
                        $contaPagarObj->setNegociodocumento($novoAtcDocumento['negociodocumento']);
                        $contaPagarObj->setNumerodocumento($entity->getNumerodocumento());
                        $contaPagarObj->setDatadocumento($entity->getEmissaodocumento());
    
                        $this->atcscontasapagarService->update($tenant, $id_grupoempresarial, $entity->getNegocio()->getNegocio(), $logged_user, $contaPagarObj);
                    }
                }

                // Commito alterações
                $this->getRepository()->commit();

                // Atualizo url com permissão do s3 ao retorno
                $novoAtcDocumento['url'] = $this->uploadFilesService->getUrl($novoAtcDocumento['url'], $this->s3BucketPath);

                // Retorno novo documento do atendimento
                return $novoAtcDocumento;
            } catch(\Exception $e){
                $this->getRepository()->rollBack();
                throw $e;
            }
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * @return array
    */
    public function getAtcsDocumentosSemMalotes($id, $id_grupoempresarial, $tenant)
    {    
        $listaDocumentos = $this->getRepository()->getAtcsDocumentosSemMalotes($id, $id_grupoempresarial, $tenant);

        // Adiciono url do documento
        $this->uploadFilesService->adapter = $this->adapter;

        foreach ($listaDocumentos as &$documento) {
            $documento['url_documento'] = $this->uploadFilesService->getUrl($documento['url_documento'], $this->s3BucketPath);
        }
        
        return $listaDocumentos;
    }

    /**
     * @param string  $tenant
     * @param string  $id_grupoempresarial
     * @param string  $logged_user
     * @param \Nasajon\MDABundle\Entity\Crm\Atcsdocumentos $entity
     * @return string
     * @throws \Exception
     */
    public function baixardocumento($tenant, $id_grupoempresarial, \Nasajon\MDABundle\Entity\Crm\Atcsdocumentos $entity){
        try {
            $documentoBinario = $this->uploadFilesService->getFileFromUrl($entity->getUrl());
            return $documentoBinario;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
    * @param string  $tenant
    * @param \Nasajon\MDABundle\Entity\Crm\Atcsdocumentos $entity
    * @return string
    * @throws \Exception
    */
    public function delete($tenant, $id_grupoempresarial, $logged_user, \Nasajon\MDABundle\Entity\Crm\Atcsdocumentos $entity){
        try {
            // Inicia transasão
            $this->getRepository()->begin();

            //Valido se o status do documento é aprovado, recusado ou Pré-aprovado
            if (in_array($entity->getStatus(), [2, 4, 5])) {
                throw new \LogicException("Não é possível deletar o documento quando o mesmo está aprovado/recusado/pré-aprovado");
            }

            
            // Valida se não possui vínculo com conta a pagar que já esteja sendo processado
            $filter = new Filter();
            $arrFilterExpression = [
                new FilterExpression('negociodocumento', 'eq', $entity->getNegociodocumento()),
            ];
            $filter->setFilterExpression($arrFilterExpression);
            $arrContaspagar = $this->atcscontasapagarService->findAll($tenant, $id_grupoempresarial, $entity->getNegocio()->getNegocio(), $filter);

            for ($i=0; $i < count($arrContaspagar); $i++) { 
                $contaPagarObj = $this->atcscontasapagarService->fillEntity($arrContaspagar[$i]);
             
                // Se a conta a pagar vinculada já foi enviada para processamento, retorno exceção com motivo de não poder excluir
                if ($contaPagarObj->getItemprocessarcontapagar()->getItemprocessarcontapagar() != null) {
                    throw new LogicException('O documento não pode ser excluído, pois está vinculado a uma conta a pagar que já foi enviada para processamento.', 1);
                    break;
                } else {
                    // Atualizo dados do documento null
                    $contaPagarObj->setNegociodocumento(null);
                    $contaPagarObj->setNumerodocumento(null);
                    $contaPagarObj->setDatadocumento(null);

                    $this->atcscontasapagarService->update($tenant, $id_grupoempresarial, $entity->getNegocio()->getNegocio(), $logged_user, $contaPagarObj);
                }
            }

            // Chamo função de exclusão do atc documento
            $response = $this->getRepository()->delete($tenant, $id_grupoempresarial, $logged_user, $entity);                           
            
            // Commito alterações
            $this->getRepository()->commit();

            // Retorno resposta
            return $response;
        }catch(\Exception $e){
            $this->getRepository()->rollBack();
            throw $e;
        }
    }
}