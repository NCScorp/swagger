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
use Nasajon\AppBundle\Enum\EnumAcao;
use Nasajon\AppBundle\Entity\Crm\ListadavezconfiguracoesEmLote;
use Nasajon\AppBundle\Form\Crm\ListadavezconfiguracoesEmLoteDefaultType;
use Nasajon\MDABundle\Controller\Crm\ListadavezconfiguracoesController as ParentController;

/**
 * Crm\Listadavezconfiguracoes controller.
 */
class ListadavezconfiguracoesController extends ParentController
{
    /**
     * Sobrescrito para alterar o denyAccessUnlessGranted para implementar o permissionamento
     *
     * @FOS\Get("/lista-da-vez-configuracoes/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function indexAction(Filter $filter = null, Request $request){
        $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZCONFIGURACOES);
        return parent::indexAction($filter, $request);
    }

    /**
     * Cria um novo Crm\Listadavezconfiguracoes entity para cada item do array.
     *
     * @FOS\Post("/lista-da-vez-configuracoes/salvarlote/", defaults={ "_format" = "json" })
    */
    public function salvarLoteAction(Request $request){
        try {
            // Só executa funcionalidade se possuir permissão
            $this->denyAccessUnlessGranted(EnumAcao::LISTADAVEZCONFIGURACOES);

            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');   
            $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
            $id_grupoempresarial = $this->get('nasajon_mda.fixed_attributes')->get('id_grupoempresarial');

            //Crio entity com os dados recebidos
            $entity = new ListadavezconfiguracoesEmLote();
            $entity->setTenant($tenant);
            $form = $this->createDefaultEmLoteForm($entity, 'POST', 'insert');
            $form->handleRequest($request);
            $this->denyAccessUnlessGranted(AbstractVoter::CREATE, $entity);

            if ($form->isValid()) {
                $service = $this->getService();

                //Crio entity com os dados originais
                $entityOriginal = new ListadavezconfiguracoesEmLote();
                $entityOriginal->setTenant($tenant);

                $arrListaDaVezConfiguracoes = $service->findAll($tenant, $id_grupoempresarial);

                for ($i=0; $i < count($arrListaDaVezConfiguracoes) ; $i++) { 
                    $entityArr = $arrListaDaVezConfiguracoes[$i];
                    $obj = $this->getService()->fillEntity($entityArr);

                    $entityOriginal->addListaDaVezConfiguracao($obj);
                }

                //Chamo função para atualizar configuração da lista da vez
                $service->salvarListaDaVezConfiguracao($tenant, $logged_user, $id_grupoempresarial, $entity, $entityOriginal);
                return new JsonResponse();
            }else{
                return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
            }
        } catch (LogicException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch(RepositoryException $e){
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);            
        }
    }

    /**
     * Creates a form to create a Crm\ListadavezconfiguracoesEmLote entity.
     *
     * @param ListadavezconfiguracoesEmLote $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createDefaultEmLoteForm(ListadavezconfiguracoesEmLote $entity, $method = "POST", $action = 'insert')
    {
        $form = $this->get('form.factory')
                     ->createNamedBuilder(NULL, ListadavezconfiguracoesEmLoteDefaultType::class, $entity, array(
                         'method' => $method,
                         'action' => $action,
                     ))
                     ->getForm();
        return $form;
    }
}
