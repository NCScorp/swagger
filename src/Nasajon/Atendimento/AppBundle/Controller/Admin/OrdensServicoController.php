<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\OrdensServicoController as ParentController;
use Nasajon\MDABundle\Entity\Servicos\OrdensServico;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class OrdensServicoController extends ParentController {

    /**
     *
     * @FOS\Get("/ordensservico/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/OrdensServico:index.html.twig');
    }

    /**
     * Displays a form to create a new Servicos\OrdensServico entity.
     *
     * @FOS\Get("/ordensservico/template/show.html", defaults={ "_format" = "html" })
     */
    public function templateShowAction(Request $request) {
        $entity = new OrdensServico();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/OrdensServico:show.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Servicos\OrdensServico entity.
     *
     * @FOS\Get("/ordensservico/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {
        $entity = new OrdensServico();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/OrdensServico:form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Servicos\OrdensServico entity.
     *
     * @FOS\Get("/ordensservico/template/form-deslocamentoextra.html", defaults={ "_format" = "html" })
     */
    public function templateFormDeslocamentoextraAction(Request $request) {
        $entity = new OrdensServico();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/OrdensServico:form-deslocamentoextra.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }
    
    /**
     * Displays a form to create a new Servicos\OrdensServico entity.
     *
     * @FOS\Get("/ordensservico/template/form-dadosadicionais.html", defaults={ "_format" = "html" })
     */
    public function templateFormDadosadicionaisAction(Request $request) {
        $entity = new OrdensServico();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/OrdensServico:form-dadosadicionais.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }
    
    /**
     * Displays a form to create a new Servicos\OrdensServico entity.
     *
     * @FOS\Get("/ordensservico/template/form-visita.html", defaults={ "_format" = "html" })
     */
    public function templateFormVisitaAction(Request $request) {
        $entity = new OrdensServico();
        $form = $this->createCreateForm($entity);
        return $this->render('NasajonAtendimentoAppBundle:Admin/OrdensServico:form-visita.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * 
     * @FOS\Put("/ordensservico/encerrar/{id}", defaults={ "_format" = "json" })     
     */
    public function encerrarAction(Request $request, $id) {

        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

            $entityArr = $this->getRepository()->find($id, $tenant);
            $entity = $this->getRepository()->fillEntity($entityArr);
            
            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);  

            $resposta = $this->getRepository()->encerrar($tenant, $logged_user, $entity);
            return new JsonResponse();
            
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Servicos\OrdensServico entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                                "message" => $e->getMessage(),
                                "entity" => $e->getEntity(),
            ]));
        }
    }

}
