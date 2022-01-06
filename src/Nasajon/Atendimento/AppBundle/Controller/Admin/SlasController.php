<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\Admin\SlasController as ParentController;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\Atendimento\AppBundle\Form\Admin\SlasType;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Slas;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * @FuncaoProvisao({"ADMIN"})
 */
class SlasController extends ParentController {
    
    /**
     * Lists all Atendimento\Admin\Slas entities.
     *
     * @FOS\Get("/slas/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction( Filter $filter = null, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
//        $entity = new Slas();
//        $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
        
        $entities = $this->getRepository()->findAll($tenant, $filter);
        
        foreach ($entities as &$sla) {
            $sla['condicoes_ex'] = $this->getRepository()->slacondicoesRepo->findAll($tenant, $sla['sla'], 0);
            $sla['condicoes_in'] = $this->getRepository()->slacondicoesRepo->findAll($tenant, $sla['sla'], 1);
        }
        
        $response = new JsonResponse();
        $response->setData($entities);
        
        return $response;

    }
    
  /**
   * Creates a form to create a Atendimento\Admin\SlasType entity.
   * @param Slas $entity The entity
   * @return Form The form
   */
  public function createCreateForm(Slas $entity, $method = "POST") {

    $form = $this->get('form.factory')
            ->createNamedBuilder(NULL, SlasType::class, $entity, array(
                'method' => $method
            ))
            ->getForm();
    $form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Salvar'));

    return $form;
  }

  /**
   * @FOS\Get("/slas/template/index.html", defaults={ "_format" = "html" })     
   */
  public function templateAction(Request $request) {
    return $this->render('NasajonAtendimentoAppBundle:Admin/Slas:index.html.twig');
  }

  /**
   * 
   * @FOS\Get("/slas/template/form.html", defaults={ "_format" = "html" })     
   */
  public function templateFormAction(Request $request) {
    return $this->render('NasajonAtendimentoAppBundle:Admin/Slas:form.html.twig');
  }
  
  /**
   *
   * @FOS\Post("/sla/reordenar/{id}/", defaults={ "_format" = "json" })
   */
  public function reordenarAction($id, Request $request) {
    try {
      $ordem = $request->get('ordem');
      $entity = new Slas();
      $entity->setSla($id);
      $entity->setOrdem($ordem);

      $this->getRepository()->reordenar($entity);

      return new JsonResponse();
    } catch (\Doctrine\ORM\NoResultException $e) {
      throw $this->createNotFoundException('Erro ao reordenar.');
    }
  }

}
