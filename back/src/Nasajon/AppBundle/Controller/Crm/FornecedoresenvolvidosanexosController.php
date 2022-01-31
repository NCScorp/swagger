<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidosanexos;
use Nasajon\AppBundle\Form\Crm\FornecedoresenvolvidosanexosDefaultType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Controller\Crm\FornecedoresenvolvidosanexosController as ParentController;

/**
 * Crm\Fornecedoresenvolvidosanexos controller.
 */
class FornecedoresenvolvidosanexosController extends ParentController
{
    /**
     * Sobrescrito para chamar form customizado, onde adiciono a propriedade referente ao anexo.
     * Creates a form to create a Crm\Fornecedoresenvolvidosanexos entity.
     *
     * @param Fornecedoresenvolvidosanexos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultForm(Fornecedoresenvolvidosanexos $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(NULL, FornecedoresenvolvidosanexosDefaultType::class, $entity, array(
                'method' => $method,
                'action' => $action,
            ))->getForm();
        return $form;
    }

    /**
     * Sobrescrito para invalidar rota de criação
     * Creates a new Crm\Fornecedoresenvolvidosanexos entity.
     * 
     * @FOS\Post("/{fornecedorenvolvido}/fornecedoresenvolvidosanexos/", defaults={ "_format" = "json" })
    */
    public function createAction($fornecedorenvolvido, Request $request){
        return new JsonResponse(["message" => "Rota indisponível"], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * Sobrescrito para invalidar rota de remoção
     * Deletes a Crm\Fornecedoresenvolvidosanexos entity.
     * 
     * @FOS\Delete("/{fornecedorenvolvido}/fornecedoresenvolvidosanexos/{id}", defaults={ "_format" = "json" })
    */
    public function deleteAction($fornecedorenvolvido, Request $request, $id){
        return new JsonResponse(["message" => "Rota indisponível"], JsonResponse::HTTP_BAD_REQUEST);
    }
}