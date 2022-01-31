<?php

namespace Nasajon\AppBundle\Controller\Crm;

use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Nasajon\MDABundle\Form as Form;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Nasajon\MDABundle\Entity\Crm\Responsabilidadesfinanceiras;

use Nasajon\AppBundle\Entity\Crm\ResponsabilidadesFinanceirasEmLote;
use Nasajon\AppBundle\Form\Crm\ResponsabilidadesfinanceirasEmLoteDefaultType;
use Nasajon\MDABundle\Form\Crm\ResponsabilidadesfinanceirasDefaultType;
use Nasajon\MDABundle\Controller\Crm\ResponsabilidadesfinanceirasController as ParentController;

/**
 * Crm\Responsabilidadesfinanceiras controller.
 */
class ResponsabilidadesfinanceirasController extends ParentController
{
    /**
     * Cria um novo Crm\Responsabilidadesfinanceiras entity para cada item do array.
     *
     * @FOS\Post("/{atc}/responsabilidadesfinanceiras/salvarlote/", defaults={ "_format" = "json" })
    */
    public function salvarLoteAction($atc, Request $request){
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');   
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            //Crio entity com os dados recebidos
            $entity = new ResponsabilidadesFinanceirasEmLote();
            $entity->setTenant($tenant);
            $entity->setNegocio($atc);
            $form = $this->createDefaultEmLoteForm($entity, 'POST', 'insert');
            $form->handleRequest($request);
            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            if ($form->isValid()) {
                $repository = $this->getService();

                //Crio entity com os dados originais
                $entityOriginal = new ResponsabilidadesFinanceirasEmLote();
                $entityOriginal->setTenant($tenant);
                $entityOriginal->setNegocio($atc);

                $arrResponsabilidadesFinanceiras = $repository->findAll($tenant, $atc, $id_grupoempresarial);

                for ($i=0; $i < count($arrResponsabilidadesFinanceiras) ; $i++) { 
                    $entityArr = $arrResponsabilidadesFinanceiras[$i];
                    $obj = $this->getService()->fillEntity($entityArr);

                    $entityOriginal->addResponsabilidadeFinanceira($obj);
                }
                
                //Chamo função para atualizar responsabilidade financeira
                $repository->salvarResponsabilidadeFinanceira($atc,$tenant,$logged_user, $id_grupoempresarial , $entity, $entityOriginal);
                return new JsonResponse();
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        } catch(LogicException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Creates a form to create a Crm\ResponsabilidadesFinanceirasEmLote entity.
     *
     * @param ResponsabilidadesfinanceirasEmLote $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultEmLoteForm(ResponsabilidadesfinanceirasEmLote $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                     ->createNamedBuilder(NULL, ResponsabilidadesfinanceirasEmLoteDefaultType::class, $entity, array(
                         'method' => $method,
                         'action' => $action,
                     ))
                     ->getForm();
        return $form;
    }

    /**
     * Creates a form to create a Crm\Responsabilidadesfinanceiras entity.
     *
     * @param Responsabilidadesfinanceiras $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultForm(Responsabilidadesfinanceiras $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                     ->createNamedBuilder(NULL, ResponsabilidadesfinanceirasDefaultType::class, $entity, array(
                         'method' => $method,
                         'action' => $action,
                     ))
                     ->getForm();
        return $form;
    }
}
