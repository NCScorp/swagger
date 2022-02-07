<?php

namespace AppBundle\Traits\Meurh;

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
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Exception\S3Exception;

/**
 * Meurh\Solicitacoesdocumentos controller.
 *
 */
trait SolicitacoesdocumentosTrait
{

    /**
     * @FOS\Get("/{id}/documentos")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : {"solicitacaodocumento":"solicitacaodocumento"}})
     */
    public function documentosListAction($id, Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = new Solicitacoesdocumentos();
            $this->validateFilterDocumento($filter);

            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);

            $entities = $this->getSolicitacoesdocumentosService()->findAllBySolicitacao($id, $tenant, $filter);

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Finds and displays a Meurh\Solicitacoesdocumentos entity.
     * @FOS\Get("/{id}/documentos/{solicitacaodocumento}", defaults={ "_format" = "json" })
     */
    public function documentosGetAction($solicitacaodocumento, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $this->validateId($solicitacaodocumento);
            $entity = $this->getSolicitacoesdocumentosService()->find($solicitacaodocumento, $tenant);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getSolicitacoesdocumentosService()->fillEntity($entity));
            $response = new JsonResponse($entity);
            return $response;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_NOT_FOUND);
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Finds and displays a Meurh\Solicitacoesdocumentos entity.
     * @FOS\Get("/{id}/documentos/{solicitacaodocumento}/download", defaults={ "_format" = "json" })
     */
    public function documentosDownloadAction($solicitacaodocumento, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $estabelecimento = $this->get('nasajon_mda.fixed_attributes')->get('estabelecimento');

            $this->validateId($solicitacaodocumento);

            $entity = $this->getSolicitacoesdocumentosService()->find($solicitacaodocumento, $tenant);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getSolicitacoesdocumentosService()->fillEntity($entity));

            return $this->getSolicitacoesdocumentosService()->getFileContent($entity, $tenant);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return $this->handleView($this->view([], JsonResponse::HTTP_NOT_FOUND));
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_NOT_FOUND);
        } catch (AccessDeniedHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_FORBIDDEN);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Creates a new Meurh\Solicitacoesdocumentos entity.
     * @FOS\Post("/{id}/documentos", defaults={ "_format" = "json" })
     */
    public function documentosCreateAction(Request $request)
    {
        try {
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = new Solicitacoesdocumentos();
            $entity->setTenant($tenant);

            $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, \AppBundle\Form\Meurh\SolicitacoesdocumentosDefaultType::class, $entity, array(
                    'method' => "POST",
                    'action' => 'insert'
                ))
                ->getForm();
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            if ($form->isValid()) {
                $repository = $this->getSolicitacoesdocumentosService();
                $retorno = $repository->insertTemp($logged_user, $tenant, $entity);

                return new JsonResponse($retorno, JsonResponse::HTTP_CREATED);
            } else {
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (AccessDeniedHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_FORBIDDEN);
        } catch (S3Exception $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletes a Meurh\Solicitacoesdocumentos entity.
     * @FOS\Delete("/{id}/documentos/{solicitacaodocumento}", defaults={ "_format" = "json" })
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : {"solicitacaohistorico":"solicitacaohistorico"}})
     * 
     */
    public function documentosDeleteAction(Request $request, $solicitacaodocumento)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $this->validateId($solicitacaodocumento);

            $entity = $this->getSolicitacoesdocumentosService()->findObject($solicitacaodocumento, $tenant);
            $entity->setSolicitacaohistorico($request->get('solicitacaohistorico'));
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $entity);

            $this->getSolicitacoesdocumentosService()->delete($tenant, $entity);

            return new JsonResponse();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw $this->createNotFoundException('Unable to find Meurh\Solicitacoesdocumentos entity.');
        } catch (InvalidIdException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_NOT_FOUND);
        } catch (RepositoryException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (AccessDeniedHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage(), 'id' => $solicitacaodocumento], JsonResponse::HTTP_FORBIDDEN);
        } catch (S3Exception $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @return \Nasajon\MDABundle\Service\Meurh\SolicitacoesdocumentosService;
     */
    public function getSolicitacoesdocumentosService()
    {
        return $this->get('Nasajon\MDABundle\Service\Meurh\SolicitacoesdocumentosService');
    }


    public function validateFilterDocumento(Filter $filter)
    {
        if (!$filter) {
            return true;
        }

        $countExpressions = [];
        $validationErrors = [];

        foreach ($filter->getFilterExpression() as $filterExpression) {
            switch ($filterExpression->getField()) {
                case 'solicitacaodocumento':
                    if (!in_array($filterExpression->getCondition(), ['eq',  'neq',])) {
                        throw new InvalidFilterException("campo {$filterExpression->getField()} não aceita a condição {$filterExpression->getCondition()}.");
                    }


                    if ($filterExpression->getValue()) {
                        $constraint = new Assert\Uuid(array('message' => 'solicitacaodocumento não é um uuid válido.'));
                        $validator = Validation::createValidator();


                        foreach ($validator->validate($filterExpression->getValue(), $constraint) as $validation) {
                            if (!empty($validation->getMessage())) {
                                array_push($validationErrors, $validation->getMessage());
                            }
                        }
                    }
                    break;
                case 'solicitacao':
                    if (!in_array($filterExpression->getCondition(), ['eq',])) {
                        throw new InvalidFilterException("campo {$filterExpression->getField()} não aceita a condição {$filterExpression->getCondition()}.");
                    }


                    if ($filterExpression->getValue()) {
                        $constraint = new Assert\Uuid(array('message' => 'solicitacao não é um uuid válido.'));
                        $validator = Validation::createValidator();


                        foreach ($validator->validate($filterExpression->getValue(), $constraint) as $validation) {
                            if (!empty($validation->getMessage())) {
                                array_push($validationErrors, $validation->getMessage());
                            }
                        }
                    }
                    break;
                default:
                    throw new InvalidFilterException("campo {$filterExpression->getField()} não tem filtro configurado.");
            }
        }

        if (count($validationErrors) > 0) {
            throw new InvalidFilterException(implode(',', $validationErrors));
        }
    }
}
