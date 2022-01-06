<?php

namespace Nasajon\Atendimento\AppBundle\Controller;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Util\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nasajon\LoginBundle\Security\User\ContaUser;

class AssetsController extends BaseController {

    /**
     *
     * @FOS\Get("/download/{id}")
     */
    public function downloadAction(Request $request, $id) {
        try {
         
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $anexo = $this->get("nasajon_mda.ns_anexosmodulos_repository")->findObject($id, $tenant, 0, 0);
            $documentoged = $this->get("nasajon_mda.ns_documentosged_repository")->findObject($anexo->getDocumentoged(), $tenant);
            $entityPai = null;

            switch ($anexo->getModulodoanexo()) {
                case \Nasajon\Atendimento\AppBundle\Service\UploadService::ANEXO_MODULO_ATENDIMENTO:
                    $entityPai = $this->get("nasajon_mda.atendimento_admin_solicitacoes_repository")->findObject($anexo->getIdmodulodoanexo(), $tenant);
                    break;
                case \Nasajon\Atendimento\AppBundle\Service\UploadService::ANEXO_MODULO_FOLLOWUP:
                    $entityPai = $this->get("nasajon_mda.atendimento_admin_followups_repository")->findObject($anexo->getIdmodulodoanexo(), null, null);
                    break;
                default:
                    throw new \Exception('Entidade pai não suportada.');
            }

            $hash = $request->query->get('hash') == $documentoged->getHash() ? true : false ;
            
            if(!$this->getUser() && !$hash){
                return new Response('', Response::HTTP_FORBIDDEN);
            }else if($this->getUser() && !$hash){
                $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entityPai);    
            }
            
            $nomeArquivo = StringUtils::removeCaracteresInvalidosNaAWS($anexo->getNome());
            
            $command = $this->get('aws.s3')->getCommand('GetObject', [ 
                'Bucket' => getenv('s3_bucket_name'), 
                'Key' => $this->container->getParameter('anexos_adapter_path').'/'.$documentoged->getUuidarquivo(),
                'ResponseContentDisposition' => 'attachment; filename='.$nomeArquivo
            ]);
            
            $presignedRequest = $this->get('aws.s3')->createPresignedRequest($command, "+1 hour");
            $presignedUrl = (string) $presignedRequest->getUri();            
            return $this->redirect($presignedUrl);
        } catch (NoResultException $ex) {
            throw $this->createNotFoundException();
        } catch (DriverException $e) {
            throw $this->createNotFoundException();
        } catch (\Symfony\Component\Security\Core\Exception\AccessDeniedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            throw $this->createNotFoundException();
        }
    }

}
