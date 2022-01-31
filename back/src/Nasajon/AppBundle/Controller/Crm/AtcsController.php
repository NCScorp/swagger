<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
use Nasajon\MDABundle\Entity\Crm\Atcs;
use Nasajon\MDABundle\Entity\Financas\Projetos;
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\AppBundle\Form\Crm\AtcsDefaultType;
use Nasajon\AppBundle\Form\Crm\AtcsBaixardocumentoType;
use Nasajon\AppBundle\Form\Crm\AtcsEnviaratendimentoemailType;
use Nasajon\AppBundle\Form\Crm\AtcsEnviardocumentosporemailType;

/**
 * Crm\Atcs controller.
 */
class AtcsController extends \Nasajon\MDABundle\Controller\Crm\AtcsController {

    /**
     * Sobreescrito para impedir a criação de um negócio sem responsavel financeiro, e a criação de negócio com mais de um responsavel financeiro como principal
     *
     * @FOS\Post("/atcs/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request) {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Atcs();
            $entity->setTenant($tenant);
            $entity->setIdGrupoempresarial($id_grupoempresarial); 

            if ( ($request->get('negociopai') !== null) && 
                 ($request->get('negociopai')['projeto'] !== null) ) {

                $negocioPai = new Atcs();                    
                $negocioPai->setNegocio($request->get('negociopai')['negocio']);
                $negocioPai->setTenant($tenant);
                $negocioPai->setIdGrupoempresarial($id_grupoempresarial);

                $projeto = new Projetos();
                $projeto->setProjeto($request->get('negociopai')['projeto']['projeto']);
                $projeto->setNome($request->get('negociopai')['projeto']['nome']);
                
                $negocioPai->setProjeto($projeto);

                $entity->setNegociopai($negocioPai); 
            }


            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::ATCS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                /* Sobreescrito para impedir a criação de um negócio sem responsavel financeiro, e a criação de negócio com mais de um responsavel financeiro como principal */
                if (empty($entity->getResponsaveisfinanceiros()[0]->getResponsavelfinanceiro())) {
                    throw new RepositoryException;
                }
                $rastreio = 0;
                foreach ($entity->getResponsaveisfinanceiros() as $principal) {
                    if ($principal->getprincipal() == true) {
                        $rastreio++;
                    }
                }
                if ($rastreio > 1 || $rastreio === 0) {
                    throw new RepositoryException;
                }

                 /* Sobreescrito para impedir a criação de um negócio sem responsavel financeiro, e a criação de negócio com mais de um responsavel financeiro como principal */
                $retorno = $repository->insert($logged_user, $tenant, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Put("/atcs/{id}", defaults={ "_format" = "json" })
    */
    public function putAction( Request $request, $id)
    {
        try{        
                        
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
                        
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
                        
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
            
            $this->validateId($id);

            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);            
                        
            $this->denyAccessUnlessGranted(EnumAcao::ATCS_PUT);  

            $editForm = $this->createDefaultForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
                        if ($editForm->isValid()) {
                $this->getService()->update($logged_user,$tenant, $entity, $originalEntity);
                return new JsonResponse();
            }else{
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Crm\Atcs entity.');
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

    /**
     * Creates a form to create a Crm\Atcs entity.
     *
     * @param Atcs $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultForm(Atcs $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                        ->createNamedBuilder(NULL, AtcsDefaultType::class, $entity, array(
                            'method' => $method,
                            'action' => $action,
                        ))
                        ->getForm();
        return $form;
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     * Finds and displays a Crm\Atcs entity.
     *
     * @FOS\Get("/atcs/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::ATCS_GET);

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
          
        }
    }          
    /**
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * 
     * @FOS\Post("/atcs/{id}/atcStatusNovo")
    */
    public function atcStatusNovoAction( $id, Request $request) {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusNovo($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * @FOS\Post("/atcs/{id}/atcStatusEmAtendimento")
     */
    public function atcStatusEmAtendimentoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusEmAtendimento($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted
     *
     * @FOS\Get("/atcs/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "negocio","negociopai","cliente","localizacaopaisnome","localizacaoestadonome","localizacaomunicipionome","status","possuiseguradora","area","datacriacao","dataedicao", }})
     */
    public function indexAction(Filter $filter = null, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new Atcs();

            $this->validateFilter($filter);

            $this->denyAccessUnlessGranted(EnumAcao::ATCS_INDEX);

            $entities = $this->getService()->findAll($tenant, $id_grupoempresarial, $filter);

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
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * 
     * @FOS\Post("/atcs/{id}/atcStatusFechado")
     */
    public function atcStatusFechadoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusFechado($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * @FOS\Post("/atcs/{id}/atcStatusReaberto")
     */
    public function atcStatusReabertoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusReaberto($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * @FOS\Post("/atcs/{id}/atcStatusFinalizado")
     */
    public function atcStatusFinalizadoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusFinalizado($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * @FOS\Post("/atcs/{id}/atcStatusCancelado")
     */
    public function atcStatusCanceladoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusCancelado($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * Sobrescrito para retornar o status e statusLabel após a alteração do status.
     * @FOS\Post("/atcs/{id}/atcStatusDescancelado")
     */
    public function atcStatusDescanceladoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $response = $this->getService()->atcStatusDescancelado($tenant, $id_grupoempresarial, $logged_user, $entity);
            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
    * @FOS\Post("/atcs/{id}/geraContrato")
    */
    public function geraContratoAction( $id, Request $request) {

        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
            
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $formFactory = $this->get('form.factory');

            $form = $this->createContratoForm($entity, 'Post', 'geraContrato');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                return $this->getService()->geraContrato($tenant,$id_grupoempresarial,$logged_user,$entity);
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            // return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }  catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }    
    }

    /**
    * @FOS\Post("/atcs/{id}/geraContratoTaxaAdministrativa")
    */
    public function geraContratoTaxaAdministrativaAction( $id, Request $request) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
            
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::CONTRATOSTAXASADM_GERENCIAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createGeracontratotaxaadmForm($entity, 'Post', 'geracontratotaxaadm');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                return $this->getService()->geraContratoTaxaAdministrativa($tenant,$id_grupoempresarial,$logged_user,$entity);
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            // return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
    * @FOS\Post("/atcs/{id}/salvaContratoTaxaAdministrativa")
    */
    public function salvaContratoTaxaAdministrativaAction( $id, Request $request) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
            
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::CONTRATOSTAXASADM_GERENCIAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createGeracontratotaxaadmForm($entity, 'Post', 'geracontratotaxaadm');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                return $this->getService()->salvaContratoTaxaAdministrativa($tenant,$id_grupoempresarial,$logged_user,$entity);
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            // return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }


    /**
    * @FOS\Post("/atcs/{id}/excluiContratoTaxaAdministrativa")
    */
    public function excluiContratoTaxaAdministrativaAction( $id, Request $request) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant'); 
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); 
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
            
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(EnumAcao::CONTRATOSTAXASADM_GERENCIAR);

            $formFactory = $this->get('form.factory');

            $form = $this->createGeracontratotaxaadmForm($entity, 'Post', 'geracontratotaxaadm');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                return $this->getService()->excluiContratoTaxaAdministrativa($tenant,$id_grupoempresarial,$logged_user,$entity);
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            // return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * @FOS\Get("/atcs/{id}/gerarRelatorioPrestadora/{fornecedor}")
     */
    public function gerarRelatorioPrestadoraAction($id, $fornecedor, Request $request)
    {
        try {
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            return $this->getService()->gerarRelatorioPrestadora($id_grupoempresarial, $tenant, $logged_user, $entity, $fornecedor);
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * @FOS\Get("/atcs/{id}/gerarRelatorioSeguradora/{fornecedor}")
     */
    public function gerarRelatorioSeguradoraAction($id, $fornecedor, Request $request)
    {
        try {
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            return $this->getService()->gerarRelatorioSeguradora($id_grupoempresarial, $tenant, $logged_user, $entity, $fornecedor);
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Creates a form to create a Crm\Atcs entity.
     *
     * @param Atcs $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createEnviaratendimentoemailForm(Atcs $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                     ->createNamedBuilder(NULL, AtcsEnviaratendimentoemailType::class, $entity, array(
                         'method' => $method,
                         'action' => $action,
                     ))
                     ->getForm();
        return $form;
    }

    /**
     * Creates a form to create a Crm\Atcs entity.
     *
     * @param Atcs $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createEnviardocumentosporemailForm(Atcs $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                     ->createNamedBuilder(NULL, AtcsEnviardocumentosporemailType::class, $entity, array(
                         'method' => $method,
                         'action' => $action,
                     ))
                     ->getForm();
        return $form;
    }

    /**
    * @FOS\Post("/atcs/{id}/baixardocumento")
        */
    public function baixardocumentoAction( $id, Request $request) {
        try {    
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');   
            
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            
            $entity = $this->getService()->fillEntity($entityArr);
            
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $formFactory = $this->get('form.factory');

            $form = $this->createBaixardocumentoForm($entity, 'Post', 'baixardocumento');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $relatorio = $this->getService()->baixardocumento($tenant, $id_grupoempresarial, $entity);
                // Informo que o retorno é do tipo pdf
                $response = new Response(
                    $relatorio,
                    Response::HTTP_OK,
                    ['content-type' => 'application/pdf']
                );
                return $response;
                return new JsonResponse();
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (\LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
    * @FOS\Post("/atcs/{id}/enviaratendimentoemail")
    */
    public function enviaratendimentoemailAction( $id, Request $request) {
        try {         
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
                
            $entityArr = $this->getService()->find($id , $tenant, $id_grupoempresarial);
            
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $formFactory = $this->get('form.factory');

            $form = $this->createEnviaratendimentoemailForm($entity, 'Post', 'enviaratendimentoemail');
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $this->getService()->enviaratendimentoemail($tenant, $id_grupoempresarial, $entity, $logged_user);
                return new JsonResponse();
                                                    
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (\LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @FOS\Post("/atcs/{id}/indexRotasGoogleDirections")
     */
    public function indexRotasGoogleDirectionsAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entityArr = [];
            $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
            if( strlen($id) === 36 || preg_match($UUIDv4,$id) ) {
                $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);
            }
            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);
            $formFactory = $this->get('form.factory');
            $form = $this->createRotasGoogleDirectionsForm($entity, 'Post', 'indexRotasGoogleDirections');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $response = $this->getService()->indexRotasGoogleDirections($tenant, $id_grupoempresarial, $entity);
                return new JsonResponse($response, JsonResponse::HTTP_OK);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (\LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @FOS\Post("/atcs/{id}/enviardocumentosporemail")
     */
    public function enviardocumentosporemailAction($id, Request $request)
    {

        try {


            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');


            $entityArr = $this->getService()->find($id, $tenant, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $formFactory = $this->get('form.factory');

            $form = $this->createEnviardocumentosporemailForm($entity, 'Post', 'enviardocumentosporemail');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getService()->enviardocumentosporemail($tenant, $id_grupoempresarial, $entity);
                return new JsonResponse();

            }
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));

        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        } catch (\LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

    }
}