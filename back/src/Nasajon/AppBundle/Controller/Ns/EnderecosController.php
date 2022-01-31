<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


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
use Nasajon\MDABundle\Entity\Ns\Enderecos;

use Nasajon\MDABundle\Controller\Ns\EnderecosController as ParentController;

/**
 * Ns\Enderecos controller.
 */
class EnderecosController extends ParentController
{
    /**
     *
     * @return \Nasajon\AppBundle\Service\Common\GoogleMapsService
     */
    public function getGoogleMapsService(){
        return $this->get('Nasajon\AppBundle\Service\Common\GoogleMapsService');
    }
    
    /**
     * Lists all NsEnderecos entities.
     *
     * @FOS\Get("/enderecos_googlemaps/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "nome","endereco", }})
    */
    public function indexGooglePlacesAction( Filter $filter = null, Request $request)
    {
        try{
            $endereco = $request->get('filter')->getKey();

            $entity = new Enderecos();
            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
            
            $entities = $this->getGoogleMapsService()->findAllGooglePlaces($endereco);
            // $entities = [];

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
     * Finds and displays a Ns\Enderecos entity.
     *
     * @FOS\Get("/enderecos_googlemaps/{id}", defaults={ "_format" = "json" })
    */
    public function getPlaceAction( $id, Request $request)
    {
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            
            $entity = $this->getGoogleMapsService()->find($id);
            
            // $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getService()->fillEntity($entity));

            $response = new JsonResponse($entity);                        
            return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id ], JsonResponse::HTTP_NOT_FOUND);            
        }
        
    }
}
