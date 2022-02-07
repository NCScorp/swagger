<?php

namespace AppBundle\Controller\Meurh;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use LogicException;

use Nasajon\MDABundle\Request\FilterExpression;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nasajon\MDABundle\Http\FormErrorJsonResponse;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\MDABundle\Type\InvalidIdException;
use Nasajon\MDABundle\Type\RepositoryException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

use Nasajon\MDABundle\Controller\Meurh\RecibospagamentosController as ParentController;

use Nasajon\MDABundle\Entity\Meurh\Recibospagamentos;
use Symfony\Component\Validator\Constraints\DateTime;

/**
* Sobrescrito por causa de permissão e dos pdfs
*
*/
class RecibospagamentosController extends ParentController{

    /**
    * Sobrescrito para receber o filtro de trabalhado
    * @todo parar de usar trabalhador e considerar construtor
    *
    * @FOS\Get("/recibospagamentos")
    * @ParamConverter("filter", class="Filter", converter="filter_converter", options={"filterfields" : {"trabalhador":"trabalhador"}})
    */
    public function indexAction( Filter $filter = null, Request $request){
       return parent::indexAction($filter, $request);  
    }
    
    /**
    * Return pdf file
    *
    * @FOS\Get("/recibospagamentos/abririnforme")
    */
    public function abririnformeAction(Request $request) {
        try {
            $entity = new Recibospagamentos();
            $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
            $id = $request->get('id');
            $url = $request->get('url');
            $opcao = $request->get('opcao');
            $nomeArquivo = $request->get('nomearquivo');
            return $this->getService()->getPdfContent($url, $opcao, $nomeArquivo,$id);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }
    
    /**
    * Return pdf file
    *
    * @FOS\Post("/recibospagamentos/downloadinformeslote")
    */
    public function downloadinformesloteAction(Request $request) {
        $entity = new Recibospagamentos();
        $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);
        
        $entities = $request->get('entities');
        $trabalhador = $request->get('trabalhador');
        //        $response = new JsonResponse();
        //        $response->setData($entities);
        
        $cache_dir = $this->get('kernel')->getCacheDir();
        
        return $this->getService()->getZipPdfsContent($entities, $trabalhador, $cache_dir);
    }
}