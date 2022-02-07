<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


namespace AppBundle\Controller\Persona;

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
use Nasajon\MDABundle\Entity\Persona\Tarifasconcessionariasvts;
use Nasajon\MDABundle\Controller\Persona\TarifasconcessionariasvtsController as ParentController;
use Nasajon\MDABundle\Request\FilterExpression;

/**
 * Persona\Tarifasconcessionariasvts controller.
 */
class TarifasconcessionariasvtsController extends ParentController
{



    /**
     * Lists all PersonaTarifasconcessionariasvts entities.
     *
     * @FOS\Get("/tarifasconcessionariasvts/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
         */
    public function indexAction( Filter $filter = null, Request $request)
    {
        try{  
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            /*Sobrescrevendo pare remover as tarifas já selecionadas na solicitação*/
            $tarifa_excluir = $request->get('tarifa_excluir');
            if(!empty($tarifa_excluir) && count($tarifa_excluir) > 0){
                if(empty($filter)){
                    $filter = new Filter();
                }
                foreach ($tarifa_excluir as $key => $tarifa) {
                    $filter->addToFilterExpression(new FilterExpression('tarifaconcessionariavt', 'neq', $tarifa));
                }
            }
            /*Sobrescrevendo pare remover as tarifas já selecionadas na solicitação*/
            $entity = new Tarifasconcessionariasvts();        
            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
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

}
