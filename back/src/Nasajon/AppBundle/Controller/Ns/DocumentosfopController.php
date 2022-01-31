<?php
namespace Nasajon\AppBundle\Controller\Ns;

use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;

use FOS\RestBundle\Controller\FOSRestController;

use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Form as Form;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Ns\Documentosfop;

// Utilizados
use Nasajon\MDABundle\Request\FilterExpression;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Controller\Ns\DocumentosfopController as ParentController;

/**
 * Ns\Documentosfop controller.
 */
class DocumentosfopController extends ParentController
{
    /**
     * Lists all NsDocumentosfop entities.
     *
     * @FOS\Get("/documentosfop/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : { "sistema","tenant", }})
    */
    public function indexAction( Filter $filter = null, Request $request)
    {
        // Se tiver filtro, analiso se o filtro de tenant está com o código em vez do número de identificação e faço a troca
        if ($filter != null){
            $arrFilterExpression = $filter->getFilterExpression();
            $arrNewExpressions = [];

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $tenant_codigo = $this->get('nasajon_mda.fixed_attributes')->get('tenant_codigo');

            foreach ($arrFilterExpression as $expression) {
                $newExpression = $expression;

                if ($expression->getField() == 'tenant' && $expression->getValue() == $tenant_codigo) {
                    $newExpression = new FilterExpression('tenant', $expression->getCondition(), $tenant);
                }

                $arrNewExpressions[] = $newExpression;
            }

            $filter->setFilterExpression($arrNewExpressions);
        }

        // Chamo index do parent
        return parent::indexAction($filter, $request);
    }

}