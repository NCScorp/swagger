<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin\Configuracoes;

use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Request\Filter;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Arquivos;
use Nasajon\Atendimento\AppBundle\Form\Admin\ArquivosType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Nasajon\Atendimento\AppBundle\Builder\SQLExpressionBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\ArquivosVoter;
use Nasajon\MDABundle\Controller\Atendimento\Admin\ArquivosController as ParentController;

/**
 * @FuncaoProvisao({"ADMIN"})
 */
class ArquivosController extends ParentController 
{
    /**
     * Lists all Atendimento\Admin\Arquivos entities.
     *
     * @FOS\Get("/arquivos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     * @FuncaoProvisao({"ADMIN"})
     */
    public function indexAction( Filter $filter = null, Request $request)
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $entity = new Arquivos();
        
        $this->denyAccessUnlessGranted(ArquivosVoter::INDEX, $entity);
               
        $entities = $this->getRepository()->findAll($tenant, $filter);

        $response = new JsonResponse();
        $response->setData($entities);
        
        return $response;
    }
  
    /**
     * Creates a form to create a Atendimento\Admin\Arquivos entity.
     *
     * @param Arquivos $entity The entity
     *
     * @return Form The form
     */
    public function createCreateForm(Arquivos $entity, $method = "POST") 
    {
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, ArquivosType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add('submit', SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * @FOS\Get("/arquivos/template/index.html", defaults={ "_format" = "html" })     
     */
    public function templateAction(Request $request) 
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        if (!$this->getRepository()->verificaPermissao($tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }

        return $this->render('NasajonAtendimentoAppBundle:Admin/Configuracoes/Arquivos:index.html.twig');
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Artigos entity.
     * @FOS\Get("/artigos/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) 
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        if (!$this->getRepository()->verificaPermissao($tenant)) {
            return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Default/403.html.twig');
        }

        $entity = new Arquivos();
        $form = $this->createCreateForm($entity);

        return $this->render(
            'NasajonAtendimentoAppBundle:Admin/Configuracoes/Arquivos:form.html.twig', 
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Creates a new Atendimento\Admin\Arquivos entity.
     *
     * @FOS\Post("/arquivos/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request) 
    {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $entity = new Arquivos();

        $entity->setTenant($tenant);

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($request->get('todosclientes') == 0) {
            $objBuilder = new SQLExpressionBuilder();
            $arrFilter = array(
                "todosclientes" => 0,
                "regrasin" => $request->get('arquivosregrasin'),
                "regrasex" => $request->get('arquivosregrasex')
            );
            $objCompositeExpression = $objBuilder->build($arrFilter, null);
            $strExpression = str_replace("cliente_", ":", $objCompositeExpression->__toString());
            $entity->setRegravisualizacao($strExpression);
        }

        $this->denyAccessUnlessGranted(ArquivosVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($tenant, $logged_user, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     * Edits an existing Atendimento\Admin\Arquivos entity.
     *
     * @FOS\Put("/arquivos/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id) 
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getRepository()->find($id, $tenant);
            $entity = $this->getRepository()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(ArquivosVoter::UPDATE, $entity);

            $editForm = $this->createCreateForm($entity, "PUT");
            $editForm->handleRequest($request);

            if ($request->get('todosclientes') == 0) {
                $objBuilder = new SQLExpressionBuilder();
                $arrFilter = array(
                    "todosclientes" => 0,
                    "regrasin" => $request->get('arquivosregrasin'),
                    "regrasex" => $request->get('arquivosregrasex')
                );
                $objCompositeExpression = $objBuilder->build($arrFilter, null);
                $strExpression = str_replace("cliente_", ":", $objCompositeExpression);
                $entity->setRegravisualizacao($strExpression);
            } else {
                $entity->setRegravisualizacao(null);
            }

            if ($editForm->isValid()) {
                $this->getRepository()->update($tenant, $logged_user, $entity);

                return new JsonResponse();
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Arquivos entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                                "message" => $e->getMessage(),
                                "entity" => $e->getEntity(),
            ]));
        }
    }

    /**
     * Deletes a Atendimento\Admin\Arquivos entity.
     *
     * @FOS\Delete("/arquivos/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction( Request $request, $id)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');  
            $entity = $this->getRepository()->findObject($id, $tenant);

            $this->denyAccessUnlessGranted(ArquivosVoter::DELETE, $entity);
            $this->getRepository()->delete($tenant, $entity);

            return new JsonResponse();
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Arquivos entity.');
        }
    }

    /**
     * @FOS\Post("/arquivos/{id}/reordenar")
     */
    public function reordenarAction($id, Request $request) 
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $ordem = $request->get('ordem');
            $entity = new Arquivos();
            $entity->setArquivo($id);
            $entity->setOrdem($ordem);

            $this->getRepository()->reordenar($entity);

            return new JsonResponse();
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Erro ao reordenar.');
        }
    }
}