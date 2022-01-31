<?php


namespace Nasajon\AppBundle\Controller\Crm;

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
use Nasajon\MDABundle\Entity\Crm\Templatespropostas;
use Nasajon\MDABundle\Controller\Crm\TemplatespropostasController as ParentController;


class TemplatespropostasController extends ParentController
{


    /**
     * Deletes a Crm\Templatespropostas entity.
     *
     * @FOS\Delete("/{templatepropostagrupo}/templatespropostas/{id}", defaults={ "_format" = "json" })
     */
    public function deleteAction($templatepropostagrupo, Request $request, $id)
    {
        try {

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            $this->validateId($id);

            $entity = $this->getService()->findObject($id, $tenant, $id_grupoempresarial, $templatepropostagrupo);
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $entity);

            $this->getService()->delete($tenant, $id_grupoempresarial, $entity);

            return new JsonResponse();
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Crm\Templatespropostas entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function validateId($id)
    {
        $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if (strlen($id) < 36 || !preg_match($UUIDv4, $id)) {
            throw new InvalidIdException();
        }
    }


    /**
     * Encontra TODOS os templatesfuncoes e templatesfamilias de um dado templateproposta.
     *
     * @FOS\Get("/{templatepropostagrupo}/templatespropostas/{id}/getTemplatesFamiliasFuncoes", defaults={ "_format" = "json" })
     */
    public function getTemplatesFamiliasFuncoesAction($templatepropostagrupo, $id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');
            
            $entity = new Templatespropostas();
            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);

            $templatesFamiliasFuncoes = $this->getService()->getTemplatesFamiliasFuncoesTemplateProposta($tenant, $id_grupoempresarial, $id);
            $response = new JsonResponse($templatesFamiliasFuncoes);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $id], JsonResponse::HTTP_NOT_FOUND);
        }
    }

}
