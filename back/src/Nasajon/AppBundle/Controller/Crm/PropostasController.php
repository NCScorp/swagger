<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Entity\Crm\Propostas;

/**
 * Crm\PropostasController controller.
 */
class PropostasController extends \Nasajon\MDABundle\Controller\Crm\PropostasController{
	/**
	 * Sobrescrito porque a criação de proposta não possui formulário
	 *
	 * @FOS\Post("/{atc}/propostas/", defaults={ "_format" = "json" })
	*/
	public function createAction($atc, Request $request)
	{
		try{
											
			$logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');            
			$tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
			$id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

			$entity = new Propostas();  
			$entity->setTenant($tenant);
			$entity->setNegocio($atc);
            $entity->setIdGrupoempresarial($id_grupoempresarial);  

			$this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

			$repository = $this->getService();
			$retorno = $repository->insert($atc, $logged_user, $tenant, $id_grupoempresarial, $entity);

			return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);

		}catch(RepositoryException $e){
				return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
		}
	}
}
