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
use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\Fornecedoresenvolvidos controller.
 */
class FornecedoresenvolvidosController extends \Nasajon\MDABundle\Controller\Crm\FornecedoresenvolvidosController
{
 
    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Post("/{atc}/fornecedoresenvolvidos/", defaults={ "_format" = "json" })
    */
    public function createAction($atc, Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $constructors = $this->verificateConstructors($tenant, $atc, $id_grupoempresarial);

            $entity = new Fornecedoresenvolvidos();

            $entity->setTenant($tenant);
            $entity->setNegocio($constructors['negocio']);
            $entity->setIdGrupoempresarial($id_grupoempresarial);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORESENVOLVIDOS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                $retorno = $repository->insert($atc, $logged_user, $tenant, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Delete("/{atc}/fornecedoresenvolvidos/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction($atc, Request $request, $id)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $atc, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::FORNECEDORESENVOLVIDOS_DELETE);

            $this->getService()->delete($entity->getNegocio()->getNegocio(),$id_grupoempresarial, $logged_user, $tenant, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Fornecedoresenvolvidos entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Retorna lista de fornecedores envolvidos no atendimento, com detalhes de advertências e contatos.
     * Dados utilizados no Accordion de Prestadores de Serviço envolvidas, na ediçãod o Atendimento comercial
     * 
     * @FOS\Get("/{atc}/fornecedoresenvolvidosdetalhes/")
     */
    public function indexAtcFornecedoresDetalhesAction($atc, Request $request){
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entities = $this->getService()->findAllAtcFornecedoresDetalhes($tenant,$atc,$id_grupoempresarial);

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } catch(InvalidFilterException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
    }
    
    /**
     * Retorna lista de fornecedores envolvidos no atendimento, com detalhes para exibição na ficha financeira.
     * 
     * @FOS\Get("/{atc}/{proposta}/fornecedoresenvolvidosfichafinanceira/")
     */
    public function indexFornecedoresenvolvidosFichafinanceiraAction($atc, $proposta, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entities = $this->getService()->findAllFornecedoresenvolvidosFichafinanceira($tenant,$atc,$proposta, $id_grupoempresarial);

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } catch(InvalidFilterException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
    }

    /**
    * @FOS\Post("/{negocio}/fornecedoresenvolvidos/{id}/aprovarOrcamentosNegocioFornecedor")
         */
        public function aprovarOrcamentosNegocioFornecedorAction($negocio, $id, Request $request) {

            try {
                   
                                
                $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
                                
                $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
                                
                $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
                
                $entityArr = $this->getService()->find($id , $tenant, $negocio, $id_grupoempresarial);
                
                $entity = $this->getService()->fillEntity($entityArr);

                $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
    
                $formFactory = $this->get('form.factory');
    
                $form = $this->createAprovarForm($entity, 'Post', 'aprovarOrcamentosNegocioFornecedor');
                $form->handleRequest($request);
                
                if ($form->isValid()) {
                    $this->getService()->aprovarOrcamentosNegocioFornecedor($negocio,$logged_user,$tenant,$id_grupoempresarial,$entity);
                    return new JsonResponse([], JsonResponse::HTTP_OK);
                }else{
                    return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
                }
    
            } catch(LogicException $e ) {
                return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);
            } catch (NoResultException $e) {
                return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
            }catch(RepositoryException $e){
                return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
            }
                catch (\Doctrine\ORM\OptimisticLockException $e) {
                return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
            } 
    }

    /**
     * Reabre todos os orçamentos do fornecedor no atendimento.
     * Sobrescrito para adicionar catch de LogicException
     * @FOS\Post("/{negocio}/fornecedoresenvolvidos/{id}/reabrirorcamentosnegociofornecedor")
     */
    public function reabrirorcamentosnegociofornecedorAction($negocio, $id, Request $request) {
        try {               
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');               
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
                    
            $entityArr = $this->getService()->find($id , $tenant, $negocio, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
    
            $this->denyAccessUnlessGranted(EnumAcao::ORCAMENTOS_REABRIR);
            // $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $this->getService()->reabrirorcamentosnegociofornecedor($negocio,$logged_user,$tenant,$id_grupoempresarial,$entity);
            return new JsonResponse();
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
    * @FOS\Post("/{negocio}/fornecedoresenvolvidos/{id}/fornecedorenvolvidoatualizarconfiguracaodescontos")
    */
    public function fornecedorenvolvidoatualizarconfiguracaodescontosAction($negocio, $id, Request $request) {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            
            $entity = new Fornecedoresenvolvidos();

            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial);
            $entity->setNegocio($negocio);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $formFactory = $this->get('form.factory');

            $form = $this->createAtualizarconfiguracaodescontosForm($entity, 'Post', 'fornecedorenvolvidoatualizarconfiguracaodescontos');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $retorno = $this->getService()->fornecedorenvolvidoatualizarconfiguracaodescontos($negocio,$logged_user,$tenant,$id_grupoempresarial,$entity);
                return new JsonResponse($retorno);                       
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }

        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }
                catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }
}
