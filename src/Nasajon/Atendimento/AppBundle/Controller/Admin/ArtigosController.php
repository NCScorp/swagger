<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Admin\ArtigosController as ArtigosParentController;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Artigos;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\Atendimento\AppBundle\Form\Admin\ArtigosType;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class ArtigosController extends ArtigosParentController {

    /**
     *
     * @FOS\Get("/artigos/{id}", defaults={ "_format" = "json" })
     */

    public function getAction( $id, Request $request){
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $categoria = $request->get('categoria');
            $followup = $request->get('followup');
            $status = $request->get('status');
            $fixarnotopo = $request->get('fixarnotopo');
            $tags = $request->get('tags');
                            
            $entity = $this->getRepository()->find($id, $tenant);

            $artigo = $entity['artigo'];
            $tenantCode = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');
            $url = $this->generateUrl('atendimento_cliente_index', ["tenant" => $tenantCode], UrlGenerator::ABSOLUTE_URL) . "/artigos/$artigo";
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getRepository()->fillEntity($entity));
            $entity['linkPublico'] = $url;

            $response = new JsonResponse($entity);                        
                            $response->setEtag($entity['lastupdate']);
                        return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Artigos entity.');
        }

    }

    /**
     * Displays a form to create a new Atendimento\Admin\Artigos entity.
     *
     * @FOS\Get("/artigos/template/show.html", defaults={ "_format" = "html" })
     */
    public function templateShowAction(Request $request) {

        return $this->render('NasajonAtendimentoAppBundle:Admin/Artigos:show.html.twig');
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Artigos entity.
     *
     * @FOS\Get("/artigos/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Artigos();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/Artigos:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Atendimento\Admin\Artigos entity.
     *
     * @FOS\Get("/artigos/template/form-modal.html", defaults={ "_format" = "html" })
     */
    public function templateFormModalAction(Request $request) {
        $entity = new Artigos();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/Artigos:form-modal.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        )); 
    }

    /**
     *
     * @FOS\Get("/artigos/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/Artigos:index.html.twig');
    }

    /**
     * Edits an existing Atendimento\Admin\Artigos entity.
     *
     * @FOS\Put("/artigos/{id}/status", defaults={ "_format" = "json" })
     */
    public function putStatusAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = $this->getRepository()->findObject($id, $tenant);

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

            $status = $request->get('status');

            if (is_bool($status)) {
                $entity->setStatus($status);
                $this->getRepository()->alterarStatus($this->getUser()->getUsername(),$tenant, $entity);

                return new JsonResponse();
            } else {
                return new JsonResponse(["erro" => "Status incorreto."], JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Artigos entity.');
        }
    }

    /**
     * Creates a new Atendimento\Admin\Artigos entity.
     *
     * @FOS\Post("/artigos/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $configuracoesService = $this->get('modelbundle.service.configuracoes');
        $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');
                
        $categoria = null;
        $followup = null;
        $status = null;
        $fixarnotopo = null;
        $tags = null;
        $categoria = null;
        $subcategoria = null;
        $secao = null;
        $orderfield = null;
        $tipoexibicao = null;
        
        $constructors = $this->verificateConstructors($tenant, $categoria, $followup, $status, $fixarnotopo, $tags, $categoria, $subcategoria, $secao, $orderfield, $tipoexibicao);
        
        $entity = new Artigos();

        $entity->setTenant($tenant);
        
        $form = $this->createCreateForm($entity, 'POST', [
            'tag_required' => $configuracoes['ARTIGO_TAG_OBRIGATORIO']
        ]);
        
        $form->handleRequest($request);
        
        $entity->setFixarNoTopo($request->get('fixarnotopo'));
        $entity->setConteudo($request->get('conteudo'));
        $entity->setCriadoPorResposta($request->get('criado_por_resposta'));
        $entity->setStatus($request->get('status'));
        $entity->setTags($request->get('tags'));
        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($tenant, $logged_user, $entity);
            $entity->setArtigo($retorno['artigo']);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }
    
    /**
     *
     * @FOS\Post("/artigos/tags", defaults={ "_format" = "json" })
     */
    public function tagsAction(Request $request)
    {
        try{
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $tags = $request->get('tags');
            $entity = [];
            
            if($tags){
               $entity = $this->getRepository()->listTags($tags, $tenant);
            }

            $response = new JsonResponse($entity);
            return $response;
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Atendimento\Artigos\Tags entity.');
        }

    }    
    

    /**
     * Edits an existing Atendimento\Admin\Artigos entity.
     *
     * @FOS\Put("/artigos/{id}", defaults={ "_format" = "json" })
     */
    public function putAction(Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $categoria = $request->query->get('categoria');
            $followup = $request->query->get('followup');
            $status = $request->query->get('status');
            $tags = $request->query->get('tags');

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getRepository()->find($id, $tenant);
            $entity = $this->getRepository()->fillEntity($entityArr);

            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

            $editForm = $this->createCreateForm($entity, 'PUT');
            $editForm->handleRequest($request);
            

            $entity->setConteudo($request->get('conteudo'));
            $entity->setStatus($request->get('status'));
            $entity->setTags($request->get('tags'));
            $entity->setTenant($tenant);
            
            if ($request->get('lastupdate') < $entity->getLastupdate()) {
                throw new \Doctrine\ORM\OptimisticLockException($this->get('translator')->trans('O que você está alterando já foi alterado anteriormente por outra pessoa, deseja sobrescrever com as suas as informações?'), $entityArr);
            }

            if ($editForm->isValid()) {
                $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
                $repository = $this->getRepository();
                $retorno = $repository->update($logged_user, $tenant, $entity);
                $retorno->setCategoria($request->request->get('categoria'));
                $retorno->setTags($request->request->get('tags'));

                return new Response($serializer->serialize($retorno, 'json'));//new JsonResponse($retorno);
            } else {
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Admin\Artigos entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                                'message' => $e->getMessage(),
                                'entity' => $e->getEntity(),
            ]));
        }
    }
    
    /**
     * Creates a form to create a Atendimento\Admin\Artigos entity.
     *
     * @param Artigos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createCreateForm(Artigos $entity, $method = "POST", $options = []){
      $options['method'] = $method;
      $form = $this->get('form.factory')
                   ->createNamedBuilder(NULL, ArtigosType::class, $entity, $options)
                   ->getForm();
      return $form;
    }
    
    
    /**
     * @FOS\Post("/artigos/{id}/reordenar")
     */
    public function reordenarAction( $id, Request $request) {

      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');                  
      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user'); 
      $entity = $this->getRepository()->findObject($id, $tenant);

      $entity->setCategoria($request->get('categoria'));
      $entity->setPosicao($request->get('posicao'));
      
      $categoria = $this->get('nasajon_mda.atendimento_categorias_repository')->findObject($request->get('categoria'), $tenant);
      
      // Seta a seção para qual o artigo está sendo movido.
      $entity->setSecao($categoria);

//      try{
          $this->getRepository()->reordenar($logged_user,$tenant,$entity);
          return new JsonResponse();
//      }
//      catch (\Exception $e) {
//          return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData(["message" => $e->getMessage()]));
//      }

    }
        
}
