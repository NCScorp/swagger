<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


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
use Nasajon\MDABundle\Entity\Crm\Templatespropostascapituloscomposicoes;
use Nasajon\MDABundle\Controller\Crm\TemplatespropostascapituloscomposicoesController as ParentController;

/**
 * Crm\Templatespropostascapituloscomposicoes controller.
 */
class TemplatespropostascapituloscomposicoesController extends ParentController
{

    
    /**
     * Deletes a Crm\Templatespropostascapituloscomposicoes entity.
     *
     * @FOS\Delete("/{templatepropostacapitulo}/templatespropostascapituloscomposicoes/{id}", defaults={ "_format" = "json" })
          */
    public function deleteAction($templatepropostacapitulo, Request $request, $id)
    {
        try{
                        
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
                        
             
            
            $this->validateId($id);
            
            $entity = $this->getService()->findObject($id , $tenant, $id_grupoempresarial, $templatepropostacapitulo);
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $entity);

                        $this->getService()->delete($tenant, $id_grupoempresarial, $entity);

            return new JsonResponse();
            
        }catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Crm\Templatespropostascapituloscomposicoes entity.');
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }
    }

}
