<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/


namespace AppBundle\Controller\Persona;

use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nasajon\MDABundle\Form as Form;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Persona\Trabalhadores;
use Nasajon\MDABundle\Controller\Persona\TrabalhadoresController as ParentController;

/**
 * Persona\Trabalhadores controller.
 */
class TrabalhadoresController extends ParentController
{
    /**
     * Retorna um resumo do trabalhador
     *
     * @FOS\Get("/trabalhadores/resumo", defaults={ "_format" = "json" })
     */
    public function resumoAction(Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = $this->getService()->resumo($tenant);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getService()->fillEntity($entity));

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * @FOS\Get("/trabalhadores/foto", defaults={ "_format" = "json" })
     */
    public function fotoAction(Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $response = new Response();
            $foto = $this->getService()->buscaFoto($tenant);
            $response->headers->set('Cache-Control', 'private');
            if (isset($foto['foto'])) {
                $image = stream_get_contents($foto['foto']);
                $response->headers->set('Content-type', 'image/jpeg');
                $response->headers->set('Content-length', strlen($image));
                $response->setContent($image);
            } else {
                throw new NotFoundHttpException("Trabalhador sem foto");
            }
            return $response;

        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Retorna endereço atual do trabalhador
     *
     * @FOS\Get("/meusdados/endereco", defaults={ "_format" = "json" })
     */
    public function enderecoAction(Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = $this->getService()->enderecocontato($tenant);

            if(gettype($entity['municipioresidencia']) == "string"){
                $entity['municipioresidencia'] = $this->get('Nasajon\MDABundle\Service\Ns\MunicipiosService')->find($entity['municipioresidencia'], $tenant);
            }
            if(gettype($entity['paisresidencia']) == "string"){
                $entity['paisresidencia'] = $this->get('Nasajon\MDABundle\Service\Ns\PaisesService')->find($entity['paisresidencia'], $tenant);
            }
            if(gettype($entity['tipologradouro']) == "string"){
                $entity['tipologradouro'] = $this->get('Nasajon\MDABundle\Service\Ns\TiposlogradourosService')->find($entity['tipologradouro'], $tenant);
            }

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getService()->fillEntity($entity));

            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Retorna o VT atual do trabalhador
     *
     * @FOS\Get("/meusdados/vt", defaults={ "_format" = "json" })
     */
    public function vtAction(Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = new Trabalhadores();
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            $entities = $this->getService()->vt($tenant);

            $response = new JsonResponse($entities);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
