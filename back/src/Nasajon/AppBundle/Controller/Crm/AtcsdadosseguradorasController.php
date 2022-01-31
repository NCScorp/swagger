<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Type\InvalidIdException;
use LogicException;
use Nasajon\MDABundle\Controller\Crm\AtcsdadosseguradorasController as ParentController;

class AtcsdadosseguradorasController extends ParentController
{

    /**
     * Creates a new Crm\Atcsdadosseguradoras entity.
     *
     * @FOS\Post("/atcsdadosseguradorasBulk/", defaults={ "_format" = "json" })
     */
    public function createBulkAction(Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = new \Nasajon\AppBundle\Entity\Crm\AtcsdadosseguradorasBulk();
            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);
            $form = $this->get('form.factory');
            $namedBuilder = $form->createNamedBuilder(NULL, \Nasajon\AppBundle\Form\Crm\AtcsdadosseguradorasBulkType::class, $entity, array(
                'method' => 'POST',
                'action' => 'insert',
            ));
            $form = $namedBuilder->getForm();

            $form->handleRequest($request);
            if ($form->isValid()) {
                $retorno = $this->getService()->createBulkAction($entity, $logged_user, $tenant);
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletes a Crm\Atcsdadosseguradoras entity.
     *
     * @FOS\Delete("/{negocio}/atcsdadosseguradoras/{id}", defaults={ "_format" = "json" })
    */
    public function deleteAction($negocio, Request $request, $id)
    {
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');      
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');            

            $this->validateId($id);
            $entity = $this->getService()->findObject($id , $negocio, $tenant, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $entity);
            $response = $this->getService()->delete($tenant, $id_grupoempresarial, $entity);

            return new JsonResponse($response);
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Crm\Atcsdadosseguradoras entity.');
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /** Edits an existing Crm\Atcsdadosseguradoras entity.
     *
     * @FOS\Put("/{atc}/atcsdadosseguradoras/{id}", defaults={ "_format" = "json" })
     */
    public function putAction($atc, Request $request, $id)
    {
        try{        
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');    
    
            $this->validateId($id);

            $entityArr = $this->getService()->find($id , $atc, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);            
                        
                        
            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);  

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
                        if ($editForm->isValid()) {
                $this->getService()->update($logged_user,$tenant, $id_grupoempresarial, $entity, $originalEntity);
                return new JsonResponse();
            }else{
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Crm\Atcsdadosseguradoras entity.');
        }catch(\Doctrine\ORM\OptimisticLockException $e){
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } 
        
        
    }
}
