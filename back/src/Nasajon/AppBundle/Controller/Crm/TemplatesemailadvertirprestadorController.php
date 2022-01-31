<?php

namespace Nasajon\AppBundle\Controller\Crm;

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
use Nasajon\MDABundle\Entity\Crm\Templatesemailadvertirprestador;
use Nasajon\MDABundle\Controller\Crm\TemplatesemailadvertirprestadorController as ParentController;

class TemplatesemailadvertirprestadorController extends ParentController
{
    /**
     * Creates a new Crm\Templatesemailadvertirprestador entity.
     *
     * @FOS\Post("/templatesemailadvertirprestador/", defaults={ "_format" = "json" })
    */
    public function createAction( Request $request)
    {
        try{   
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');       
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');      
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $entity = new Templatesemailadvertirprestador();
            $entity->setTenant($tenant);
            
            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($logged_user,$id_grupoempresarial,$tenant, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Edits an existing Crm\Templatesemailadvertirprestador entity.
     *
     * @FOS\Put("/templatesemailadvertirprestador/{id}", defaults={ "_format" = "json" })
    */
    public function putAction( Request $request, $id)
    {
        try{
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            
            $this->validateId($id);

            $entityArr = $this->getService()->find($id , $tenant);
            $entity = $this->getService()->fillEntity($entityArr);
        
            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);  

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $this->getService()->update($logged_user,$id_grupoempresarial,$tenant, $entity);
                return new JsonResponse();
            }else{
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Crm\Templatesemailadvertirprestador entity.');
        }catch(\Doctrine\ORM\OptimisticLockException $e){
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
