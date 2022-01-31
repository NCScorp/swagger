<?php

namespace Nasajon\AppBundle\Controller\Gp;

use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Form as Form;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Gp\Funcoes;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Gp\Funcoes controller.
 */
class FuncoesController extends \Nasajon\MDABundle\Controller\Gp\FuncoesController
{

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
      * @FOS\Get("/funcoes/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function indexAction( Filter $filter = null, Request $request)
    {
        try{
                       
                        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
             

            
            $entity = new Funcoes();
               
                        
            $this->denyAccessUnlessGranted(EnumAcao::FUNCOES_INDEX);
            
            $entities = $this->getService()->findAll($tenant, $filter);

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        }
        catch(InvalidFilterException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
        catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
    }

    /**
     * Creates a new Gp\Funcoes entity.
     *
     * @FOS\Post("/funcoes/", defaults={ "_format" = "json" })
          */
          public function createAction( Request $request)
          {
              try{
                              
                      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
                              
                      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
    
                  $entity = new Funcoes();        
      
                                                                  $entity->setTenant($tenant);   
                                                       
      
                  $form = $this->createDefaultForm($entity, 'POST', 'insert');
                  $form->handleRequest($request);
      
                  $this->denyAccessUnlessGranted(EnumAcao::FUNCOES_CREATE);
      
                  if ($form->isValid()) {
                      $repository = $this->getService();
                      $retorno = $repository->insert($logged_user,$tenant, $entity);
      
                      return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
      
                  }else{
                      return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
                  }
              }catch(RepositoryException $e){
                  return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
              }
          }


         /**
     * Edits an existing Gp\Funcoes entity.
     *
     * @FOS\Put("/funcoes/{id}", defaults={ "_format" = "json" })
          */
         public function putAction( Request $request, $id)
         {
             try{        
                             
                 $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
                             
                 $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
                             
                  
                 
                 $this->validateId($id);
     
                 $entityArr = $this->getService()->find($id , $tenant);
                 $entity = $this->getService()->fillEntity($entityArr);
                 $originalEntity = $this->getService()->fillEntity($entityArr);            
                             
                 $this->denyAccessUnlessGranted(EnumAcao::FUNCOES_PUT);  
     
                 $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
                 $editForm->handleRequest($request);
                 $temp = $request->get('lastupdate');
                 $entity;
                             if($request->get('lastupdate') != $entity->getLastupdate()){
                     throw new \Doctrine\ORM\OptimisticLockException($this->get('translator')->trans('O que você está alterando já foi alterado anteriormente por outra pessoa, deseja sobrescrever com as suas as informações?'), $entityArr);
                 }
                    
                             if ($editForm->isValid()) {
                     $this->getService()->update($logged_user,$tenant, $entity, $originalEntity);
                     return new JsonResponse();
                 }else{
                     return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
                 }
             }catch(\Doctrine\ORM\NoResultException $e){
                 throw $this->createNotFoundException('Unable to find Gp\Funcoes entity.');
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

         /**
     * Deletes a Gp\Funcoes entity.
     *
     * @FOS\Delete("/funcoes/{id}", defaults={ "_format" = "json" })
          */
    public function deleteAction( Request $request, $id)
    {
        try{
                        
                        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
             
            
            $this->validateId($id);
            
            $entity = $this->getService()->findObject($id , $tenant);
            $this->denyAccessUnlessGranted(EnumAcao::FUNCOES_DELETE);

                        $this->getService()->delete( $entity);

            return new JsonResponse();
            
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Gp\Funcoes entity.');
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }
    }
}
