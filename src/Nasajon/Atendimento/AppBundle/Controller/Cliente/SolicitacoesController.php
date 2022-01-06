<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Nasajon\Atendimento\AppBundle\Form\Cliente\SolicitacoesType;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\SolicitacoesController as SolicitacoesParentController;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Followups;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Solicitacoes;
use Nasajon\MDABundle\Form\Atendimento\Cliente\FollowupsType;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;

class SolicitacoesController extends SolicitacoesParentController {

    /**
     * @FOS\Get("/solicitacoes/template/widget.html", defaults={ "_format" = "html" })
     */
    public function templateWidgetAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Cliente:Solicitacoes/main.html.twig');
    }

    /**
     *
     * @FOS\Get("/solicitacoes/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $configuracoes = $em->getRepository('Nasajon\ModelBundle\Entity\Configuracoes')->findByTenant($this->get('nasajon_mda.fixed_attributes')->get('tenant'));
        $conf = [];
        foreach ($configuracoes as $configuracao) {
            $conf[$configuracao->getSistema()][$configuracao->getChave()] = $configuracao->getValor();
        }

        return $this->render('NasajonAtendimentoAppBundle:Cliente:Solicitacoes/main.html.twig', array(
                    'descricao' => $conf['ATENDIMENTO']['CHAMADOS_DESCRICAO']
        ));
    }

    /**
     * Displays a form to create a new Atendimento\Cliente\Solicitacoes entity.
     *
     * @FOS\Get("/solicitacoes/template/show.html", defaults={ "_format" = "html" })
     */
    public function templateShowAction(Request $request) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);

        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, FollowupsType::class, new Followups(), array(
                    'method' => "POST"
                ))
                ->getForm();

        return $this->render('NasajonAtendimentoAppBundle:Cliente:Solicitacoes/main.html.twig', array(
                    'camposcustomizados' => $camposcustomizados,
                    'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to create a new Atendimento\Cliente\Solicitacoes entity.
     *
     * @FOS\Get("/solicitacoes/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new Solicitacoes();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Cliente/Solicitacoes:main.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @FOS\Get("/solicitacoes/dashboard", defaults={ "_format" = "json" })
     *
     */
//    public function dashboardAction(Request $request) {
//        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//        $repository = $this->getRepository();
//
//        $solicitacoes_abertas = $repository->countAbertas(null, $tenant);
//        $respostas_novas = $repository->countRespostasNovas(null, $tenant);
//
//        $response = new JsonResponse();
//        $response->setData(array(
//            'solicitacoes_abertas' => $solicitacoes_abertas,
//            'respostas_novas' => $respostas_novas
//        ));
//
//        return $response;
//    }

    /**
     * @FOS\Get("/solicitacoes/dashboard", defaults={ "_format" = "json" })
     *
     */
    public function dashboardAction(Request $request) {

        if (!$this->getUser()) {
            return new JsonResponse(['chamados' => []]);
        }

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $repository = $this->getRepository();

        $chamados = $repository->listDashboard($tenant, $this->getUser()->getUsername());

        $response = new JsonResponse();
        $response->setData(array(
            'chamados' => $chamados
        ));

        return $response;
    }

    /*
     * Creates a form to create a Atendimento\Cliente\Solicitacoes entity.
     *
     * @param Solicitacoes $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */

    public function createCreateForm(Solicitacoes $entity, $method = "POST") {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);


        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, SolicitacoesType::class, $entity, array(
                    'method' => $method,
                    'camposcustomizados' => $camposcustomizados
                ))
                ->getForm();
        $form->add( 'submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     * Lists all Atendimento\Cliente\Solicitacoes entities.
     *
     * @FOS\Get("/solicitacoes/", defaults={ "_format" = "json" })
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction(Filter $filter = null, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        // $cliente = $request->get('cliente');
        $cliente = $request->get('cliente');

        $criador = $request->get('criador');        
        $filter->setKey(htmlentities($filter->getKey()));

        $created_at_ini = $request->get('created_at_ini');
        $email = $request->get('email');                
        $created_at_fim = $request->get('created_at_fim');                
        $camposcustomizados = $request->get('camposcustomizados');                
        $responsavel_web = $request->get('responsavel_web');
        // $constructors = $this->verificateConstructors($tenant, $cliente, $criador);
        $constructors = $this->verificateConstructors($tenant, $cliente, $email, $created_at = '', $created_at_ini , $created_at_fim ,  $camposcustomizados, $responsavel_web, null);

        $entity = new Solicitacoes();
        $entity->setTenant($tenant);
        $entity->setCliente($constructors['cliente']);
        $entity->setCreatedBy($criador);

        $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);

        if (!empty($request->get('minhas'))) {
            $criador = $this->getUser()->getUsername();
            $clientes = $this->get('nasajon_mda.atendimento_cliente_usuarios_repository')->getClientesByConta($criador, $tenant);
            $cliente = array_map(function($cl) {
                return $cl['cliente_id'];
            }, $clientes);
            array_push($cliente, null);
        } elseif (empty($request->get('minhas')) && empty($request->get('cliente'))) {
            $criador = $this->getUser()->getUsername();
            $clientes = $this->get('nasajon_mda.atendimento_cliente_usuarios_repository')->getClientesByConta($criador, $tenant);
            $cliente = array_map(function($cl) {
                return $cl['cliente_id'];
            }, $clientes);
        }

        // Pega o e-mail do usuário logado filtrar os chamados de acordo com a configuração ACESSO_CHAMADOS_VISAO_CLIENTE
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
        $emailUsuario = $logged_user['email'];

        $entities = $this->getRepository()->findAll($tenant, $cliente, $email, $created_at, $created_at_ini, $created_at_fim, $camposcustomizados, $responsavel_web, $emailUsuario, $filter);

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }


    /**
     * Retorna se atendimento não encontrado
     *
     * @FOS\Get("/atendimentos/404", defaults={ "_format" = "html" })
     */
    public function notfoundAction(Request $request) {
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Cliente/Solicitacoes/404.html.twig');
    }


    /**
     * Creates a new Atendimento\Cliente\Solicitacoes entity.
     *
     * @FOS\Post("/solicitacoes/", defaults={ "_format" = "json" })
     */
    public function createAction(Request $request)
    {
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $cliente = null;
        $email = null;

        $constructors = $this->verificateConstructors($tenant, $cliente, $email, null, null,  null, null, null, null);

        $entity = new Solicitacoes();

        $entity->setTenant($tenant);
        $entity->setCanal('portal');
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $entity->setSintoma($request->get('sintoma'));

        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

        if ($form->isValid()) {
            $repository = $this->getRepository();
            $retorno = $repository->insert($logged_user, $tenant, $entity);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }
}
