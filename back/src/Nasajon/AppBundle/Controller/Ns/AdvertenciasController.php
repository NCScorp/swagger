<?php

namespace Nasajon\AppBundle\Controller\Ns;

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
use Nasajon\MDABundle\Entity\Ns\Advertencias;
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\MDABundle\Controller\Ns\AdvertenciasController as NsAdvertenciasController;

/**
 * Ns\Advertencias controller.
 */
class AdvertenciasController extends NsAdvertenciasController
{

    /**
     * @FOS\Post("/advertencias/{id}/arquivar")
     */
    public function arquivarAction($id, Request $request)
    {

        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::ADVERTENCIAS_ARQUIVAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createArquivarForm($entity, 'Post', 'arquivar');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $action = 'arquivar';
                if ($entity->getStatus() != 0) {
                    return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
                }
                $this->getService()->arquivar($tenant, $entity);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    //  /**
    // * Sobreescrito para adição do permissionamento porém comentado pois admin não tem a permissão
    // * @FOS\Post("/advertencias/{id}/excluir")
    //      */
    //     public function excluirAction( $id, Request $request) {

    //         try {
                   
                                
    //             $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
                        
                 
                
    //             $entityArr = $this->getService()->find($id , $tenant);
                
    //             $entity = $this->getService()->fillEntity($entityArr);
    
    //             $this->denyAccessUnlessGranted(EnumAcao::ADVERTENCIAS_EXCLUIR);
    
    //             $formFactory = $this->get('form.factory');
    
    //             $form = $this->createExcluirForm($entity, 'Post', 'excluir');
    //             $form->handleRequest($request);
                
    //             if ($form->isValid()) {
    //                                     $action = 'excluir';
    //                 if($entity->getStatus() != 0){                    
    //                     return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
    //                 }
    //                                                                             $this->getService()->excluir($tenant,$entity);
    //                         return new JsonResponse();
                                                        
    //             }else{
    //                 return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
    //             }
    
    //         } catch (NoResultException $e) {
    //             return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
    //         }catch(RepositoryException $e){
    //             return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
    //         }
    //                 catch (\Doctrine\ORM\OptimisticLockException $e) {
    //             return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
    //         }
    //             }
}
