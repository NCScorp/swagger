<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Servicos\VisitaController as ParentController;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao({"ADMIN","USUARIO"})
 */
class VisitaController extends ParentController {

    /**
     * Edits an existing Servicos\Visita entity.
     *
     * @FOS\Put("/{ordemservico}/visita/{id}/check", defaults={ "_format" = "json" })
     */
    public function checkAction($ordemservico, Request $request, $id) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');


            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $type = $request->get('type');

            $entity = $this->getRepository()->findObject($id, $tenant, $ordemservico);



            $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $entity);

            $editForm = $this->createCreateForm($entity, "PUT");
            $editForm->handleRequest($request);


            if ($type == "i") {
                $this->getRepository()->ordemServicoVisitaCheckin($tenant, $logged_user, $entity);
            } elseif ($type == "o") {
                $this->getRepository()->ordemServicoVisitaCheckout($tenant, $logged_user, $entity);
            }
            $retorno = $this->getRepository()->find($id, $tenant, $ordemservico);

            return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Servicos\Visita entity.');
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            return $this->handleView($this->view(null, JsonResponse::HTTP_CONFLICT)->setData([
                                "message" => $e->getMessage(),
                                "entity" => $e->getEntity(),
            ]));
        }
    }

}
