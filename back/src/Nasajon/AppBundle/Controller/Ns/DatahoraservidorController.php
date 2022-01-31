<?php

namespace Nasajon\AppBundle\Controller\Ns;

use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Form as Form;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

/**
 * Ns\DatahoraservidorController controller.
 * Busca a data e a hora do servidor
 */
class DatahoraservidorController extends FOSRestController{

  /**
  *
  * @return \Nasajon\AppBundle\Service\Ns\DatahoraservidorService;
  */
  public function getService(){
    return $this->get('Nasajon\AppBundle\Service\Ns\DatahoraservidorService');
  }

  /**
   * @FOS\Get("/ns/datahora"), defaults={ "_format" = "json" })
  */
  public function getDataHoraServidorAction()
  {
    $service = $this->getService();
    $dataHoraServidor = $service->getDataHoraServidor();

    return $dataHoraServidor;
  }  
 
}
