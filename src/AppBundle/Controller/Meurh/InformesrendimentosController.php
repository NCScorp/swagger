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

use Nasajon\MDABundle\Controller\Meurh\InformesrendimentosController as ParentController;

use Nasajon\MDABundle\Entity\Meurh\Informesrendimentos;
use Symfony\Component\Validator\Constraints\DateTime;


use Nasajon\MDABundle\Form as Form;

/**
 * Sobrescrito por causa de métodos relacionados aos pdfs dos informes e permissão
 */
class InformesrendimentosController extends ParentController{

    /**
     * Return pdf file
     *
     * @FOS\Get("/informesrendimentos/abririnforme")
     */
    public function abririnformeAction(Request $request)
    {
        try {
            $url = $request->get('url');
            $opcao = $request->get('opcao');
            $id = $request->get('id');
            $nomeArquivo = $request->get('nomearquivo');
            return $this->getService()->getPdfContent($url, $opcao, $nomeArquivo,$id);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Return pdf file
     *
     * @FOS\Get("/informesrendimentos/downloadinformeslote")
     */
    public function downloadinformesloteAction(Request $request)
    {
        $entities = $request->get('entities');

        $cache_dir = $this->get('kernel')->getCacheDir();

        return $this->getService()->getZipPdfsContent($entities, $cache_dir);
    }


}
