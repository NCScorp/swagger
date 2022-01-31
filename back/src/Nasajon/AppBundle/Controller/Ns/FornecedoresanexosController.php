<?php

namespace Nasajon\AppBundle\Controller\Ns;

use Nasajon\MDABundle\Controller\Ns\FornecedoresanexosController as ParentController;
use Nasajon\MDABundle\Entity\Ns\Fornecedoresanexos;
use Nasajon\AppBundle\Form\Ns\FornecedoresanexosDefaultType;

class FornecedoresanexosController extends ParentController
{

    /**
     * Creates a form to create a Ns\Fornecedoresanexos entity.
     *
     * @param Fornecedoresanexos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultForm(Fornecedoresanexos $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(NULL, FornecedoresanexosDefaultType::class, $entity, array(
                'method' => $method,
                'action' => $action,
            ))
            ->getForm();
        return $form;
    }

}
