<?php

namespace Nasajon\Atendimento\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RedirectController extends BaseController {

    /**
     * @Route("/redirectnasajon", name="redirect_nasajon")
     */
    public function redirectnasajonAction() {
        return $this->redirect($this->generateUrl('atendimento_cliente_index', array('tenant' => 'nasajon')));
    }

}
