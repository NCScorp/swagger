<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Entity\Crm\Atcsdocumentos;
use LogicException;

/**
 * Crm\AtcsdocumentosController controller.
 */
class AtcsdocumentosController extends \Nasajon\MDABundle\Controller\Crm\AtcsdocumentosController
{

    /**
     * Sobreescrito para impedir a criação de um negócio sem responsavel financeiro, e a criação de negócio com mais de um responsavel financeiro como principal
     *
     * @FOS\Post("/atcsdocumentos/", defaults={ "_format" = "json" })
     */
    // public function createAction($id, Request $request) {
    public function createAction(Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Atcsdocumentos();

            $entity->setTenant($tenant);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            if ($form->isValid()) {
                $repository = $this->getService();
                $repository->atcstiposdocumentosrequisitantesService = $this->get(
                    'Nasajon\MDABundle\Service\Crm\AtcstiposdocumentosrequisitantesService'
                );

                $retorno = $repository->insert($tenant, $logged_user, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @FOS\Post("/atcsdocumentos/{id}/preAnalise")
     */
    public function preAnaliseAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $entity->setStatus($request->get('status'));
            $entity->setDescricao($request->get('descricao'));

            $this->getService()->preAnalise($tenant, $logged_user, $id_grupoempresarial, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }


    /**
     * 
     * @FOS\Get("/atcsdocumentossemmalotes/{id}/" , defaults={ "_format" = "json" })
     * 
     **/
        public function getAtcsDocumentosSemMalotesAction($id, Request $request)
        {
            try{
                           
                $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
                $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

                $this->validateId($id);
                                           
                // $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORES_INDEX);
                
                $entities = $this->getService()->getAtcsDocumentosSemMalotes($id, $id_grupoempresarial, $tenant);
    
                $response = new JsonResponse();
                $response->setData($entities);
    
                return $response;
            }catch(LogicException $e ) {
                return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
            }catch(\Doctrine\ORM\NoResultException $e){
                return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
            }catch(InvalidIdException $e){
                return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND); 
            }
        }    
    
    /**
    * @FOS\Get("/atcsdocumentos/{id}/baixardocumento")
    */
    public function baixardocumentoAction( $id, Request $request) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            // Busco binário do documento
            $binario = $this->getService()->baixardocumento($tenant, $id_grupoempresarial, $entity);

            // Retorno documento, com o seu tipo
            $response = new Response(
                $binario,
                Response::HTTP_OK,
                [
                    'content-type' => $entity->getTipomime()
                ]
            );
            return $response;
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Deletes a Crm\Atcsdocumentos entity.
     *
     * @FOS\Delete("/atcsdocumentos/{id}", defaults={ "_format" = "json" })
    */
    public function deleteAction( Request $request, $id)
    {
        try{
            return parent::deleteAction($request, $id);
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
    }
}
