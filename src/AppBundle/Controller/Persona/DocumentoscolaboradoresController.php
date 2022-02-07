<?php
/*
    Código gerado automaticamente pelo Transformer do MDA 
*/

namespace AppBundle\Controller\Persona;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use FOS\RestBundle\Controller\Annotations as FOS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Type\InvalidFilterException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use LogicException;
use Nasajon\MDABundle\Controller\Persona\DocumentoscolaboradoresController as ParentController;


/**
 * Persona\Documentoscolaboradores controller.
 */
class DocumentoscolaboradoresController extends ParentController
{
    /**
     * Listar todos comprovantes de residência do colaborador
     * 
     * @FOS\Get("/meusdados/documentoendereco/listar")
     * @ParamConverter("filter", class="Filter", converter="filter_converter", options={})
     */
    public function documentoEnderecoAction(Filter $filter = null, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            // Recuperar os documentos, se o colaborador possuir
            $documents = $this->getService()->findDocumentosEndereco($tenant);

            $response = new JsonResponse();
            $response->setData($documents);

            return $response;

        } catch(InvalidFilterException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        } catch(LogicException $e ) {
            return new JsonResponse(["message" => $e->getMessage() ], JsonResponse::HTTP_BAD_REQUEST);            
        }
    }

    /**
     * Retorna o documento do colaborador, independente do tipo
     *
     * @FOS\Get("/meusdados/documento/{id}/download", defaults={ "_format" = "json" })
     */
    public function getdocumentoAction($id, Request $request)
    {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $trabalhador = $this->get('nasajon_mda.fixed_attributes')->get('trabalhador');

            $document = $this->getService()->findByDocument($id, $tenant, $trabalhador);

            return $this->getResponsePDF($document);

        } catch (InvalidFilterException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(["message" => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    protected function getResponsePDF($documento){
        $response = new StreamedResponse(function () use($documento) {
            $file = stream_get_contents($documento['bindocumento']);
            echo $file;
        }, 200, ['Content-Type' => $documento['mime'], 'Content-Disposition' => 'inline; filename="documento.'.$documento['extensao'].'"']);

        return $response->send();
    }
}