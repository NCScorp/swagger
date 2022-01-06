<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Exception\InvalidObjectException;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\ContatosVoter;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\ContatosController as ParentController;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Contatos;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ContatosController extends ParentController {

    /**
     *
     * @FOS\Get("/contatos/template/index.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function templateAction(Request $request) {
        $this->denyAccessUnlessGranted(ContatosVoter::ACESSAR_CONTATOS_CLIENTES, new Contatos());
        
        return $this->templateShowAction($request);
    }

    /**
     * Displays a form to create a new Ns\Clientes entity.
     *
     * @FOS\Get("/contatos/template/show.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function templateShowAction(Request $request) {
        $this->denyAccessUnlessGranted(ContatosVoter::ACESSAR_CONTATOS_CLIENTES, new Contatos());

        return $this->render('NasajonAtendimentoAppBundle:Cliente:Contatos/show.html.twig');
    }

    /**
     * Displays a form to create a new Ns\Clientes entity.
     *
     * @FOS\Get("/contatos/template/form.html", defaults={ "_format" = "html" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function templateFormAction(Request $request) {
        $this->denyAccessUnlessGranted(ContatosVoter::ACESSAR_CONTATOS_CLIENTES, new Contatos());

        return $this->render('NasajonAtendimentoAppBundle:Cliente:Contatos/form.html.twig');
    }

    /**
     * Lists all Atendimento\Cliente\Contatos entities.
     *
     * @FOS\Get("/contatos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function indexAction(Filter $filter = null, Request $request) {
        $this->denyAccessUnlessGranted(ContatosVoter::ACESSAR_CONTATOS_CLIENTES, new Contatos());

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $entities = $this->getRepository()->findAll($tenant, $logged_user['email'], $filter);

        $response = new JsonResponse();
        $response->setData($entities);
        
        return $response;        
    }

    /**
     * Finds and displays a Atendimento\Cliente\Contatos entity.
     *
     * @FOS\Get("/contatos/{id}", defaults={ "_format" = "json" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function getAction($id, Request $request) {
        try {

            $this->denyAccessUnlessGranted(ContatosVoter::ACESSAR_CONTATOS_CLIENTES, new Contatos());
            
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
                        
            $entity = $this->getRepository()->find($id, $tenant);
        
            $response = new JsonResponse($entity);                        
            
            return $response;

        } catch(\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Cliente\Contatos entity.');
        }
    }

/**
     * Creates a new Atendimento\Cliente\Contatos entity.
     *
     * @FOS\Post("/contatos/", defaults={ "_format" = "json" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function createAction( Request $request) {
           
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');            
                    
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
            
            $entity = new Contatos();        
                    
            $entity->setTenant($tenant);   
            
            $form = $this->createCreateForm($entity);
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            $this->getRepository()->inserirContatos($request->request->all(), $tenant, $logged_user);
            
            return new JsonResponse();

        } catch (InvalidObjectException $e) {
            throw $e;

        } catch(\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        }
    }

    /**
     * Edits an existing Atendimento\Cliente\Contatos entity.
     *
     * @FOS\Put("/contatos/{id}", defaults={ "_format" = "json" })
     * @FuncaoProvisao({"ADMIN","USUARIO"})
     */
    public function putAction(Request $request, $id)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');  
                                    
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');               
        
            $this->getRepository()->alterarContatos($request->request->all(), $tenant, $logged_user);

            return new JsonResponse();

        } catch(\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Atendimento\Cliente\Contatos entity.');

        } catch(\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                "message" => $e->getMessage(),
                "entity" => $e->getEntity(),
            ]));
        }
    }
}