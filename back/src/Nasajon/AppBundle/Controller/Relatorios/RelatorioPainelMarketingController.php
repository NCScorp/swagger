<?php

namespace Nasajon\AppBundle\Controller\Relatorios;

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
use Nasajon\AppBundle\Enum\EnumAcao;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Crm\Negocios;

class RelatorioPainelMarketingController extends FOSRestController {
    /**
     * Retorna o service do painel de marketing
     * @return \Nasajon\AppBundle\Service\Relatorios\RelatorioPainelMarketingService
    */
    public function getService(){
        return $this->get('Nasajon\AppBundle\Service\Relatorios\RelatorioPainelMarketingService');
    }

    /**
     * Valida os filtros passados
     */
    public function validateFilter( Filter $filter ) {
        $countExpressions = [];
        $validationErrors = [];
        $arrFiltrosObrigatoriosNaoPassados = ['datainicio', 'datafinal'];
        $datainicio = null;
        $datafinal = null;
        
        if($filter != null) {
            foreach( $filter->getFilterExpression() as $filterExpression ) {           
                switch( $filterExpression->getField() ) {
                    case 'datainicio': {
                        $index = array_search($filterExpression->getField(), $arrFiltrosObrigatoriosNaoPassados);

                        if ($index > -1) {
                            array_splice($arrFiltrosObrigatoriosNaoPassados, $index, 1);
                        }

                        if( $filterExpression->getValue() ){
                            $constraint = new Assert\Date(array('message' =>'Data de início não é uma data válida.'));
                            $validator = Validation::createValidator();
                            $arrErrosValidacao = $validator->validate($filterExpression->getValue(), $constraint);

                            if (count($arrErrosValidacao) > 0) {
                                foreach($arrErrosValidacao as $validation){
                                    if( !empty($validation->getMessage()) ){
                                        array_push($validationErrors, $validation->getMessage());
                                    }
                                }
                            } else {
                                $datainicio = new \DateTime($filterExpression->getValue());
                            }
                        }

                        break;
                    }
                    case 'datafinal': {
                        $index = array_search($filterExpression->getField(), $arrFiltrosObrigatoriosNaoPassados);

                        if ($index > -1) {
                            array_splice($arrFiltrosObrigatoriosNaoPassados, $index, 1);
                        }

                        if( $filterExpression->getValue() ){
                            $constraint = new Assert\Date(array('message' =>'Data final não é uma data válida.'));
                            $validator = Validation::createValidator();
                            $arrErrosValidacao = $validator->validate($filterExpression->getValue(), $constraint);

                            if (count($arrErrosValidacao) > 0) {
                                foreach($arrErrosValidacao as $validation){
                                    if( !empty($validation->getMessage()) ){
                                        array_push($validationErrors, $validation->getMessage());
                                    }
                                }
                            } else {
                                $datafinal = new \DateTime($filterExpression->getValue());
                            }
                        }
                        break;
                    }
                    case 'campanha': {
                        if( !in_array($filterExpression->getCondition(), [ 'eq', ]) ) {
                            throw new InvalidFilterException("campo {$filterExpression->getField()} não aceita a condição {$filterExpression->getCondition()}.");
                        }
                                           
                        if( $filterExpression->getValue() ){
                            $constraint = new Assert\Uuid(array( 'message' => 'Campanha não é um uuid válido.'));
                            $validator = Validation::createValidator();
    
                            foreach($validator->validate($filterExpression->getValue(), $constraint) as $validation){
                                if( !empty($validation->getMessage()) ){
                                    array_push($validationErrors, $validation->getMessage());
                                }
                            }                    
                        }
                        break;
                    }
                    default:
                        throw new InvalidFilterException("campo {$filterExpression->getField()} não tem filtro configurado.");
                }
            }
        }
                
        // Verifico se os filtros obrigatórios foram passados
        foreach ($arrFiltrosObrigatoriosNaoPassados as $filtro) {
            array_push($validationErrors, "campo {$filtro} é obrigatório");
        }

        if ($datainicio != null && $datafinal != null) {
            $intervalo = $datainicio->diff($datafinal);

            if ($intervalo->invert == 1) {
                array_push($validationErrors, "campo datainicio deve ser menor ou igual a datafim");
            } else {
                // Pego o (intervalo em anos * 12) + intervalo em meses para ter o total de meses de diferença + 1
                $intervaloMeses = ($intervalo->y * 12) + $intervalo->m + 1;
        
                if ( ((int)$datainicio->format('d')) > ((int)$datafinal->format('d')) ) {
                    $intervaloMeses++;
                }

                if ($intervaloMeses > 12) {
                    array_push($validationErrors, "o intervalo máximo deve ser de 12 meses");
                }
            }
        }

        // Se houve algum erro de validação, gero exceção
        if( count($validationErrors) > 0 ){
            throw new InvalidFilterException(implode(', ', $validationErrors));
        }
    }

    /**
     * Retorna dados referentes ao painel de marketing
     *
     * @FOS\Get("/paineis/marketing/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "datainicio", "datafinal", "campanha", }})
    */
    public function getAction(Filter $filter = null, Request $request){
        // Só executa funcionalidade se possuir permissão
        $this->denyAccessUnlessGranted(EnumAcao::RELATORIOS_PAINEL_MARKETING);

        try{
            // Busco fixed atributes           
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            
            // Valido filtros
            $this->validateFilter($filter);

            // Preencho filtros
            $datainicio = null;
            $datafinal = null;
            $campanha = null;

            foreach( $filter->getFilterExpression() as $filterExpression ) {
                switch( $filterExpression->getField() ) {
                    case 'datainicio': {
                        $datainicio = $filterExpression->getValue();
                        break;
                    }
                    case 'datafinal': {
                        $datafinal = $filterExpression->getValue();
                        break;
                    }
                    case 'campanha': {
                        $campanha = $filterExpression->getValue();
                        break;
                    }
                }
            }

            // Busco dados referente ao painel de marketing
            $arrDadosPainel = $this->getService()->getPainelMarketing($tenant, $id_grupoempresarial, $datainicio, $datafinal, $campanha);

            $response = new JsonResponse();
            $response->setData($arrDadosPainel);

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