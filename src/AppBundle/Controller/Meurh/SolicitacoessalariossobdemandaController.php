<?php

namespace AppBundle\Controller\Meurh;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use LogicException;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Nasajon\MDABundle\Request\FilterExpression;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use AppBundle\Enum\Ns\configuracoesGeraisEnum as ConfiguracoesGeraisEnum;
use Nasajon\MDABundle\Controller\Meurh\SolicitacoessalariossobdemandaController as ParentController;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoessalariossobdemanda;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Traits\Meurh\SolicitacoesdocumentosTrait;

/**
* Sobrescrito por causa da permissão
*/
class SolicitacoessalariossobdemandaController extends ParentController
{
    use SolicitacoesdocumentosTrait;
  
    /**
     * @return \Nasajon\MDABundle\Service\Meurh\ConfiguracoesService;
     */
    public function getConfiguracoesService()
    {
        return $this->get('Nasajon\MDABundle\Service\Web\ConfiguracoesService');
    }

    /**
     * @return \Nasajon\MDABundle\Service\Persona\TrabalhadoresService;
     */
    public function getTrabalhadoresService()
    {
        return $this->get('Nasajon\MDABundle\Service\Persona\TrabalhadoresService');
    }

    /**
     * @return \Nasajon\MDABundle\Service\Persona\ValorsolicitacaoporperiodoService;
     */
    public function getValoressolicitacoesporperiodoService()
    {
        return $this->get('Nasajon\MDABundle\Service\Meurh\ValoressolicitacoesporperiodoService');
    }
  
    /**
    * Sobrescrito por da segunda verificação com o voter, para checar se o módulo está configurado
    *
    * @FOS\Get("/")
    * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
    */        
    public function indexAction(Filter $filter = null, Request $request)
    {
        
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');
            
            $constructors = $this->verificateConstructors($tenant, $trabalhador);
            
            $entity = new Solicitacoessalariossobdemanda();
            
            $entity->setTrabalhador($constructors['trabalhador']);
            
            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
            
            $entities = $this->getService()->findAll($tenant, $trabalhador, $filter);
            
            $response = new JsonResponse();
            $response->setData($entities);
            
            return $response;
        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Finds and displays a Meurh\Solicitacoessalariossobdemanda entity.
     *
     * @FOS\Get("/{id}", defaults={ "_format" = "json" })
     */
    public function getAction( $id, Request $request)
    {
        return parent::getAction($request, $id);
    }
    
    /**
    * Sobrescrito por da segunda verificação com o voter, para checar se o módulo está configurado
    *
    * @FOS\Post("/", defaults={ "_format" = "json" })
    */
    public function createAction(Request $request)
    {
        try {
            
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');            
            
            $constructors = $this->verificateConstructors($tenant, $trabalhador);
            
            $entity = new Solicitacoessalariossobdemanda();
            
            $entity->setTenant($tenant);
            $entity->setTrabalhador($constructors['trabalhador']);
            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);
            
            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);
            
            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($trabalhador,$logged_user,$tenant, $entity);
                
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (UnexpectedValueException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * O método é responsável por retornar as configuracoes do trabalhador logado referentes a simulação de adiantamento de salário sob demanda. Os dados são: [valorminimo, valordisponivel, valorpendente, valoraprovado]
     * 
     * @FOS\Get("/configuracoes/", defaults={ "_format" = "json" })
     */
    public function configuracoesAction(Request $request) {
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');

            // Busca dados em web configs
            $retorno["valorminimo"] = $this->getConfiguracoesService()->getValor($tenant, configuracoesGeraisEnum::SOLIC_SAL_SOBDEMANDA_MIN);
            
            // Busca dados em trabalhador
            $trabalhador_arr = $this->getTrabalhadoresService()->find($trabalhador, $tenant);
            $retorno["salarioestimado"] = $trabalhador_arr["salarioliquidoestimado"];

            // Busca dados em valores por período (histórico mensal de valores solicitados e aprovados)
            $filter = new Filter();
            $filterExpression = $filter->getFilterExpression();
            array_push($filterExpression, new FilterExpression('trabalhador', 'eq', $trabalhador));
            array_push($filterExpression, new FilterExpression('ano', 'eq', date("Y")));
            array_push($filterExpression, new FilterExpression('mes', 'eq', date("n")));
            $filter->setFilterExpression($filterExpression);

            $result = $this->getValoressolicitacoesporperiodoService()->findAll($tenant, $filter);
            $retorno["valordisponivel"] = isset($result[0]) ? $result[0]["valordisponivel"] : 0;
            $retorno["valorpendente"] = isset($result[0]) ? $result[0]["valorpendente"] : 0;
            $retorno["valoraprovado"] = isset($result[0]) ? $result[0]["valoraprovado"] : 0;
            $retorno["valorbloqueado"] = isset($result[0]) ? $result[0]["valorbloqueado"] : 0;

            // Busca dados em web configs
            $retorno["valorminimo"] = $this->getConfiguracoesService()->getValor($tenant, ConfiguracoesGeraisEnum::SOLIC_SAL_SOBDEMANDA_MIN);
            
            // Calcula valor máximo
            $retorno["valormaximo"] = $retorno["valordisponivel"] - $retorno["valorpendente"] - $retorno["valoraprovado"];

            // 
            $provedor = $this->getConfiguracoesService()->getValor($tenant, configuracoesGeraisEnum::SOLIC_SAL_SOBDEMANDA_PROVEDOR);
            $retorno["provedores"] = json_decode($provedor);
            
            $response = new JsonResponse($retorno);
            return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);            
        }catch(InvalidIdException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);            
        }
    }
}