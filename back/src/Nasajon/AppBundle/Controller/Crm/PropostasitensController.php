<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use LogicException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Entity\Crm\Propostasitens;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Crm\PropostasitensController controller.
 */
class PropostasitensController extends \Nasajon\MDABundle\Controller\Crm\PropostasitensController
{

    /**
     * Sobrescrito para passar o negócio até o service. Muitas informações do negócio são importantes na hora de criação do item contrato e no momento não foi possivel injetar o service de négocio no service de propostaitem
     * @todo verificar por que o negócio não está sendo carregado no objeto entity pelo MDA
     * @FOS\Post("/{negocio}/{proposta}/propostasitens/", defaults={ "_format" = "json" })
     */
    public function createAction($negocio, $proposta, Request $request)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $constructors = $this->verificateConstructors($tenant, $negocio, $proposta, $id_grupoempresarial);
            $entity = new Propostasitens();

            $entity->setTenant($tenant);
            $entity->setNegocio($constructors['negocio']);
            $entity->setProposta($constructors['proposta']);
            $entity->setIdGrupoempresarial($id_grupoempresarial);
            $propostaCapituloTemp = $request->request->get('propostacapitulo');
            $propostaCapitulo = EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Crm\Propostascapitulos', $propostaCapituloTemp);
            $entity->setPropostacapitulo($propostaCapitulo);

            $form = $this->createDefaultForm($entity, 'POST', 'insert');
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_CREATE);

            if ($form->isValid()) {
                $repository = $this->getService();
                $entity->setNegocio($constructors['negocio']); //verificar por que o MDA não está colocando o objeto negócio na entidade
                $entity->setProposta($constructors['proposta']);

                $entity->setPrevisaodatahorainicio($request->request->get('previsaodatahorainicio'));
                $entity->setPrevisaodatahorafim($request->request->get('previsaodatahorafim'));


                $retorno = $repository->insert($proposta, $logged_user, $tenant, $negocio, $id_grupoempresarial, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Finds and displays a Crm\Propostasitens entity.
     *
     * @FOS\Get("/{negocio}/{proposta}/propostasitens/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($negocio, $proposta, $id, Request $request)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->find($id, $tenant, $negocio, $proposta, $id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_GET);

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Deletes a Crm\Propostasitens entity.
     *
     * @FOS\Delete("/{negocio}/{proposta}/propostasitens/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction($negocio, $proposta, Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');


            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $negocio, $proposta, $id_grupoempresarial);
            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_CREATE);

            $this->getService()->delete($tenant, $proposta, $logged_user, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Propostasitens entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @FOS\Post("/{negocio}/{proposta}/propostasitens/propostasItensVincularFornecedorBulk/"), defaults={ "_format" = "json" }, methods={"POST"})
     */
    public function vincularFornecedorBulkAction($negocio, $proposta, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $bulk = new \Nasajon\AppBundle\Entity\Crm\PropostasitensBulk();

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $bulk);

            $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, \Nasajon\AppBundle\Form\Crm\PropostasitensBulkType::class, $bulk, array(
                    'method' => 'POST',
                    'action' => 'update',
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isValid()) {
                $service = $this->getService();
                $retorno = $service->vincularFornecedorLote($negocio, $proposta, $tenant, $id_grupoempresarial, $bulk);
                return new JsonResponse($retorno, JsonResponse::HTTP_OK);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException | LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @FOS\Post("/{negocio}/{proposta}/propostasitens/propostasItensDesvincularFornecedorBulk/", defaults={ "_format" = "json" }, methods={"POST"})
     */
    public function desvincularFornecedorBulkAction($negocio, $proposta, Request $request)
    {
        try {
            //$logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');   
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial'); //R

            $bulk = new \Nasajon\AppBundle\Entity\Crm\PropostasitensBulk();

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $bulk);
            $form = $this->get('form.factory')

                ->createNamedBuilder(NULL, \Nasajon\AppBundle\Form\Crm\PropostasitensBulkType::class, $bulk, array(
                    'method' => 'POST',
                    'action' => 'update',
                ))
                ->getForm();

            $form->handleRequest($request);
            $service = $this->getService();
            $retorno = $service->desvincularFornecedorLote($negocio, $proposta, $tenant, $id_grupoempresarial, $bulk);
            return new JsonResponse($retorno, JsonResponse::HTTP_OK);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * 
     * @FOS\Post("/{negocio}/{proposta}/templateGeraItens/", defaults={ "_format" = "json" })
     */
    public function templateGeraItensAction($negocio, $proposta, Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $entity = new \Nasajon\AppBundle\Entity\Crm\TemplatespropostascapituloscomposicoesBulk();
            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);
            $form = $this->get('form.factory');
            $namedBuilder = $form->createNamedBuilder(NULL, \Nasajon\AppBundle\Form\Crm\TemplatespropostascapituloscomposicoesBulkType::class, $entity, array(
                'method' => 'POST',
                'action' => 'insert',
            ));
            $form = $namedBuilder->getForm();

            $form->handleRequest($request);
            if ($form->isValid()) {
                $retorno = $this->getService()->templateGeraItensLote($negocio, $proposta, $logged_user, $tenant,  $id_grupoempresarial, $entity);
                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*Sobreescrito para conseguir o grupo empresarial */
    /**
     * @FOS\Post("/{negocio}/{proposta}/propostasitens/{id}/propostasItensVincularFornecedor")
     */
    public function propostasItensVincularFornecedorAction($negocio, $proposta, $id, Request $request)
    {

        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getService()->find($id, $tenant, $negocio, $proposta, $id_grupoempresarial);

            $entity = $this->getService()->fillEntity($entityArr);

            $entity->getNegocio()->setIdgrupoempresarial($id_grupoempresarial);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR);

            $formFactory = $this->get('form.factory');

            $form = $this->createPropostasItensVincularFornecedorForm($entity, 'Post', 'propostasItensVincularFornecedor');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $retorno = $this->getService()->propostasItensVincularFornecedor($tenant, $entity, $id_grupoempresarial);
                return new JsonResponse($retorno, JsonResponse::HTTP_OK);
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

    /**
     * @FOS\Post("/{negocio}/{proposta}/propostasitens/{id}/propostasItensDesvincularFornecedor")
     */
    public function propostasItensDesvincularFornecedorAction($negocio, $proposta, $id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $entityArr = $this->getService()->find($id, $tenant, $negocio, $proposta, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR);
            $this->getService()->propostasItensDesvincularFornecedor($tenant, $entity, $id_grupoempresarial);
            return new JsonResponse();
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (NoResultException $e) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }

    /**
     * @FOS\Post("/{negocio}/{proposta}/propostasitens/{id}/propostasitensfornecedorescolhacliente")
     */
    public function propostasitensfornecedorescolhaclienteAction($negocio, $proposta, $id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $entityArr = $this->getService()->find($id, $tenant, $negocio, $proposta, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_CREATE);
            $formFactory = $this->get('form.factory');
            $form = $this->createPropostasitensfornecedorescolhaclienteForm($entity, 'Post', 'propostasitensfornecedorescolhacliente');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getService()->propostasitensfornecedorescolhacliente($tenant, $logged_user, $entity);
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

    /**
     * Edits an existing Crm\Propostasitens entity.
     *
     * @FOS\Put("/{negocio}/{proposta}/propostasitens/{id}", defaults={ "_format" = "json" })
     */
    public function putAction($negocio, $proposta, Request $request, $id)
    {
        try {

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entityArr = $this->getService()->find($id, $tenant, $negocio, $proposta, $id_grupoempresarial);
            $entity = $this->getService()->fillEntity($entityArr);
            $originalEntity = $this->getService()->fillEntity($entityArr);

            //Setando para null porque eu vou fazer a atribuição desses campos no service
            $entity->setPrevisaodatahorainicio(null);
            $entity->setPrevisaodatahorafim(null);

            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_CREATE);

            $editForm = $this->createEdicaoForm($entity, 'PUT', 'update');
            $editForm->handleRequest($request);
            if ($editForm->isValid()) {

                if ($request->request->get('previsaodatahorainicio') != null) {
                    $objDataHoraInicio = \DateTime::createFromFormat('Y-m-d H:i:s', $request->request->get('previsaodatahorainicio'));
                    $entity->setPrevisaodatainicio($objDataHoraInicio->format('Y-m-d'));
                    $entity->setPrevisaohorainicio($objDataHoraInicio->format('H:i:s'));
                } else {
                    $entity->setPrevisaodatainicio(null);
                    $entity->setPrevisaohorainicio(null);
                }

                if ($request->request->get('previsaodatahorafim') != null) {
                    $objDataHoraFim = \DateTime::createFromFormat('Y-m-d H:i:s', $request->request->get('previsaodatahorafim'));
                    $entity->setPrevisaodatafim($objDataHoraFim->format('Y-m-d'));
                    $entity->setPrevisaohorafim($objDataHoraFim->format('H:i:s'));
                } else {
                    $entity->setPrevisaodatafim(null);
                    $entity->setPrevisaohorafim(null);
                }

                $this->getService()->update($logged_user, $tenant, $proposta, $id_grupoempresarial, $entity, $originalEntity);
                $entity->setPrevisaodatahorainicio(null);
                $entity->setPrevisaodatahorafim(null);
                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Propostasitens entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Encontra TODAS as funcoes e familias de propostasitens de uma dada proposta.
     *
     * @FOS\Get("/{negocio}/{proposta}/getFamiliasFuncoesProposta/", defaults={ "_format" = "json" })
     */
    public function getFamiliasFuncoesPropostaAction($negocio, $proposta, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            $this->denyAccessUnlessGranted(EnumAcao::PROPOSTASITENS_GET);
            $familiasFuncoes = $this->getService()->getFamiliasFuncoesProposta($tenant, $id_grupoempresarial, $proposta);
            $response = new JsonResponse($familiasFuncoes);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
