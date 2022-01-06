<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Crm\ProximoscontatosController as ProximoscontatosParentController;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Nasajon\MDABundle\Entity\Crm\Proximoscontatos;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class ProximosContatosController extends ProximoscontatosParentController {

    /**
     *
     * @FOS\Get("/proximoscontatos/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/ProximosContatos:index.html.twig');
    }

    /**
     * Displays a form to create a new Atendimento\Admin\ProximoContato entity.
     *
     * @FOS\Get("/proximoscontatos/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Proximoscontatos();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/ProximosContatos:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Crm\Proximoscontatos entity.
     *
     * @FOS\Get("/proximoscontatos/template/show.html", defaults={ "_format" = "html" })
     */
    public function templateShowAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/ProximosContatos:show.html.twig');
    }

    /**
     * Creates a new Crm\Proximoscontatos entity.
     *
     * @FOS\Post("/proximoscontatos/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $cliente = null;
        $data = null;
        $situacao = null;
        $ematraso = null;
        $responsavel_web = null;
        $atendimento = null;

        $constructors = $this->verificateConstructors($tenant, $cliente, $data, $situacao, $ematraso, $responsavel_web, $atendimento);

        $entity = new Proximoscontatos();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        
        $entity->setTenant($tenant);
        
        if (!empty($request->get('atendimento'))) {
            $s = new Solicitacoes();            
            gettype($request->get('atendimento')) === 'string' ? 
                $s->setAtendimento($request->get('atendimento')) : //Cadastro normal
                $s->setAtendimento($request->get('atendimento')['atendimento']); //Duplicando agendamento
            $entity->setAtendimento($s);
            
            $c = new Clientes();
            $c->setCliente($request->get('cliente')['cliente']);
            $entity->setCliente($c);
        }

        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($tenant, $logged_user, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }
    
    /**
     * Lista todos os agendamentos pendentes e atrasados do usuário logado
     *
     * @FOS\Get("/proximoscontatos/pendentes", defaults={ "_format" = "json" })
     */
    public function pendentesAction(Request $request) {
        $entities = $this->getRepository()->pendentes($this->get('nasajon_mda.fixed_attributes')->get('tenant'), $this->get('nasajon_mda.fixed_attributes')->get('logged_user')['email']);
        $response = new JsonResponse();
        $response->setData($entities);
        return $response;
    }

}
