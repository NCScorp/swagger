<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Form\Admin\AtendimentosfilasType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\AtendimentosfilasController as ParentController;
use Nasajon\MDABundle\Entity\Servicos\Atendimentosfilas;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao("ADMIN")
 */
class AtendimentosfilasController extends ParentController {

    /**
     * Lists all Servicos\Atendimentoscamposcustomizados entities.
     *
     * @FOS\Get("/atendimentosfilas/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function indexAction(Filter $filter = null, Request $request) {
        return parent::indexAction($filter, $request);
    }

    /**
     *
     * @FOS\Get("/atendimentosfilas/template/index.html", defaults={ "_format" = "html" })
     */
    public function templateAction(Request $request) {
        return $this->render('NasajonAtendimentoAppBundle:Admin/Atendimentosfilas:index.html.twig');
    }

    public function createCreateForm(Atendimentosfilas $entity, $method = "POST") {

        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, AtendimentosfilasType::class, $entity, array(
                    'method' => $method
                ))
                ->getForm();
        $form->add( 'submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

        return $form;
    }

    /**
     *
     * @FOS\Get("/atendimentosfilas/template/form.html", defaults={ "_format" = "html" })
     */
    public function templateFormAction(Request $request) {

        $entity = new Atendimentosfilas();
        $form = $this->createCreateForm($entity);
        return $this->render('@NasajonAtendimentoAppBundle/Resources/views/Admin/Atendimentosfilas/form.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     *
     * @FOS\Get("/atendimentosfilas/template/form-delete.html", defaults={ "_format" = "html" })
     */
    public function templateFormDeleteAction(Request $request) {

        $entity = new Atendimentosfilas();

        return $this->render('NasajonAtendimentoAppBundle:Admin/Atendimentosfilas:form-delete.html.twig', array(
                    'count_solicitacoes' => 11,
                    'entity' => $entity
                        )
        );
    }

    /**
     * Retorna  quantas solicitações existem nesta fila
     *
     * @FOS\Get("/atendimentosfilas/{fila}/countsolicitacoes")
     */
    public function countSolicitacoesAction(Request $request, $fila) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $count = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->countSolicitacoes($tenant, $fila);

        $response = new JsonResponse();
        $response->setData(['count' => $count]);
        return $response;
    }

    /**
     * Deletes a Servicos\Atendimentosfilas entity.
     *
     * @FOS\Delete("/atendimentosfilas/{id}")
     */
    public function deleteAction(Request $request, $id) {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $entity = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->findObject($id, $tenant);
            $logged_user = json_encode($this->get('nasajon_mda.fixed_attributes')->get('logged_user'));
            $reatribuidoa = $request->get('reatribuidoa');

            if (is_null($reatribuidoa)) {
                $response = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->delete($entity);
            } else {
                $response = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->deleteUpdate($entity, empty($reatribuidoa) ? null : $reatribuidoa, $logged_user);
            }
            return new JsonResponse($response);
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Servicos\Atendimentosfilas entity.');
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     *
     * @FOS\Post("/atendimentosfilas/{id}/", defaults={ "_format" = "json" })
     */
    public function reordenarAction($id, Request $request) {
        try {
            $ordem = $request->get('ordem');
            $entity = new Atendimentosfilas();
            $entity->setAtendimentofila($id);
            $entity->setOrdem($ordem);

            $this->getRepository()->reordenar($entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Erro ao reordenar.');
        }
    }

    /**
     * Retorna  quantas solicitações existem nesta fila
     *
     * @FOS\Get("/atendimentosfilas/{observadorfila}/removeobservador", defaults={ "_format" = "html" })
     */
    public function removeObservadorAction(Request $request, $observadorfila) {

        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//
        $observadorfila = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->find($tenant, $observadorfila);

//        $observadorfila = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->find($tenant, $observadorfila);


        return $this->render('NasajonAtendimentoAppBundle:Admin/Atendimentosfilas:remove-observador.html.twig', array(
                'fila' => $fila,
                'observadorfila' => $observadorfila
            )
        );
    }

    /**
     * Retorna  quantas solicitações existem nesta fila
     *
     * @FOS\Get("/atendimentosfilas/{fila}/removeobservadorconfirmar", defaults={ "_format" = "html" })
     */
    public function removeObservadorConfirmarAction(Request $request, $observadorfila) {

//        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//
//        $count = $this->get('nasajon_mda.servicos_atendimentosfilas_repository')->removeObservador($observadorfila);
//
//        $response = new JsonResponse();
//        $response->setData(['count' => $count]);
//        return $response;

        return $this->render('NasajonAtendimentoAppBundle:Admin/Atendimentosfilas:remove-observador.html.twig', array(




                        )
        );
    }

}
