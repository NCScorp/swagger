<?php

namespace Nasajon\AppBundle\Controller\Ns\Pessoas;

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
use Nasajon\MDABundle\Entity\Ns\Pessoas\Logos;
use Nasajon\AppBundle\Form\Ns\Pessoas\LogosDefaultType;

use Nasajon\MDABundle\Controller\Ns\Pessoas\LogosController as ParentController;

/**
 * Ns\Pessoas\Logos controller.
 */
class LogosController extends ParentController
{
    /**
     * Creates a form to create a Ns\Pessoas\Logos entity.
     *
     * @param Logos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultForm(Logos $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                     ->createNamedBuilder(NULL, LogosDefaultType::class, $entity, array(
                         'method' => $method,
                         'action' => $action,
                     ))
                     ->getForm();
        return $form;
    }
}
