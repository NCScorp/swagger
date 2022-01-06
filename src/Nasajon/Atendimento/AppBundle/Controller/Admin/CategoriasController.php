<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\CategoriasController as ParentController;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Entity\Atendimento\Categorias;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;

/**
 * @FuncaoProvisao("ADMIN")
 */
class CategoriasController extends ParentController {
  
        /**
     * Edits an existing Atendimento\Categorias entity.
     *
     * @FOS\Put("/categorias/{id}", defaults={ "_format" = "json" })
     */
    public function putAction( Request $request, $id)
    {
        try{        
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');  
            $tipo = $request->get('tipo');
            $categoriapai = $request->get('categoriapai');
            $status = $request->get('status');
            $tipoordencao = $request->get('tipoordencao');
            

            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');               
                            
            $entityArr = $this->getRepository()->find($id, $tenant);
            $entity = $this->getRepository()->fillEntity($entityArr);
                        
            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);  

            $editForm = $this->createCreateForm($entity, "PUT");
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $this->getRepository()->update($logged_user,$tenant, $entity);

                return new JsonResponse();

            }else{
                return $this->handleView($this->view($editForm, JsonResponse::HTTP_BAD_REQUEST));
            }
        }catch(\Doctrine\ORM\NoResultException $e){
            throw $this->createNotFoundException('Unable to find Atendimento\Categorias entity.');
        }catch(\Doctrine\ORM\OptimisticLockException $e){
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        }
    }

    /**
     * Creates a new Atendimento\Categorias entity.
     *
     * @FOS\Post("/categorias/", defaults={ "_format" = "json" })
     */
    public function createAction( Request $request){
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
        $tipo = null;
        $categoriapai = null;
        $entity = new Categorias();        
        $entity->setTenant($tenant);   
        $entity->setCategoriapai($request->get('categoriapai'));
        $entity->setTipoordenacao($request->get('tipoordenacao'));
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($tenant,$logged_user, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);

        }else{
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }

    }
  
    
    /**
     * 
     * @FOS\Get("/categorias/template/index.html", defaults={ "_format" = "html" })     
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/Categorias:index.html.twig');
    }

    /**
     * Lists all Atendimento\Categorias entities.
     *
     * @FOS\Get("/categorias/") 
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function indexAction(Filter $filter = null, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $tipo = $request->get('tipo');
        $categoriapai = $request->get('categoriapai');
        $status = $request->get('status');

        $buscarApenasCategorias = $request->get('buscarApenasCategorias');

        if (!$buscarApenasCategorias) {
            $entities = $this->get('nasajon_mda.atendimento_categorias_repository')->findAll($tenant,$tipo,$categoriapai, $status,$filter);

            //Feito desta forma para desonerar o banco.
            foreach ($entities as $key => $entity) {
                $entities[$key]['breadcrumb'] = $entity['titulo_t2'] . ' - ' . $entity['titulo_t1'];
            }
        } else {
            $entities = $this->get('nasajon_mda.atendimento_categorias_repository')->buscarApenasTitulosDasCategorias($tenant);
        }

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }

    
    /**
     *
     * @FOS\Post("/categoria/{categoria}/", defaults={ "_format" = "json" })
     */
    public function reordenarAction($categoria, Request $request) {
//        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            
            $ordem = $request->get('ordem');
            $categoriapai = $request->get('categoriapai');
            
            $entity = new Categorias();
            $entity->setCategoria($categoria);
            $entity->setCategoriapai($categoriapai);
            $entity->setOrdem($ordem);
            $entity->setTenant($tenant);

            $this->getRepository()->reordenar($tenant, $entity);

            return new JsonResponse();
//        } catch (\Doctrine\ORM\NoResultException $e) {
//            throw $this->createNotFoundException('Erro ao reordenar.');
//        }
    }
}
