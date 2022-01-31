<?php

namespace Nasajon\AppBundle\Service\Ns;

use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Busca a data e a hora do servidor
 */
class DatahoraservidorService
{

  /**
  * @return \Nasajon\AppBundle\Repository\Ns\DatahoraservidorRepository
  */
  public function getRepository(){
    return $this->repository;
  }

  public function __construct(\Nasajon\AppBundle\Repository\Ns\DatahoraservidorRepository $repository)
  {
    $this->repository = $repository;
  }

  /**
	* @return string
	* @throws \Exception
	*/
	public function getDataHoraServidor(){
    $result = $this->getRepository()->dataHoraServidor();
    if(is_array($result)){
      return EntityHydrator::hydrate('Nasajon\AppBundle\Entity\Ns\Datahoraservidor',  $result);
    }   

  }

}
