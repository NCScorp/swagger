<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Financeiro\BoletosManager;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\TitulosController as ParentController;
use Nasajon\MDABundle\Security\Authorization\AbstractVoter;
//use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\AbstractVoter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\StreamedResponse;

class TitulosController extends ParentController {

    private const CODIGO_ISBP_ITAU = '60701190';

    /**
     * Lists all Atendimento\Cliente\Titulos entities.
     *
     * @FOS\Get("/{cliente}/titulos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction($cliente, Filter $filter = null, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $constructors = $this->verificateConstructors($tenant, $cliente);

        $entity = new Titulos();
        $entity->setCliente($constructors['cliente']);
        
        $this->denyAccessUnlessGranted(AbstractVoter::INDEX, $entity);

        $entities = $this->getRepository()->findAll($tenant, $cliente, $filter);

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }
    
    /**
     * Lists all Atendimento\Cliente\Titulos entities.
     *
     * @FOS\Get("/titulos/clientes")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function getClientesAction(Filter $filter = null, Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');

        $entity = new Clientes();
        
        $entities = $this->get('nasajon_mda.atendimento_cliente_usuarios_repository')->getClientesOfTitulosByConta($logged_user['email'], $tenant); 

        $response = new JsonResponse();
        $response->setData($entities);

        return $response;
    }


    /**
     * Finds and displays a Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos entity.
     *
     * @FOS\Get("/{id_pessoa}/titulos/{id}", defaults={ "_format" = "json" })
     */
    public function getAction($id_pessoa, $id, Request $request) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

            $entity = $this->getRepository()->find($id, $tenant, $id_pessoa);

            $entity = $this->getRepository()->fillEntity($entity);

            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $entity);

            if ($entity->getCodigobanco() == self::CODIGO_ISBP_ITAU) {
                return BoletosManager::gerar($entity);
            }
            
            return $this->getPdfContent($this->getRepository()->buscarBoleto($id, $tenant), 'Boleto - '.$entity->getVencimento().'.pdf');

        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find.');
        }
    }

    public function getPdfContent($conteudo, $nomeArquivo) {

        $content = $conteudo['documento'];
        $content = stream_get_contents($content);
       
        $response = new Response($content);
       
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $nomeArquivo . '"');

        return $response;
    }

    /**
     * Finds and displays a Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos entity.
     *
     * @FOS\Get("/{id_pessoa}/titulos/{id}/nota.{_format}", defaults={ "_format" = "html" }, requirements={"_format": "pdf|xml"})
     */
    public function notaAction($id_pessoa, $id, $_format, Request $request) {
        try {
            $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
            $entity = $this->getRepository()->find($id, $tenant, $id_pessoa);
            $this->denyAccessUnlessGranted(AbstractVoter::VIEW, $this->getRepository()->fillEntity($entity));
            $tipo = 0;

            switch ($_format) {
                case 'xml':
                    $contentType = 'text/xml';
                    if ($entity['tipo'] == 0 && $entity['modelo'] == 'NE') {
                        $tipo = 1;
                    } elseif ($entity['tipo'] == 5) {
                        $tipo = 3;
                    }
                    break;
                case 'pdf':
                    $contentType = 'application/pdf';
                    if ($entity['tipo'] == 0 && $entity['modelo'] == 'NE') {
                        $tipo = 2;
                    } elseif ($entity['tipo'] == 5) {
                        $tipo = 4;
                    }
                    break;
            }
            
            $response = $this->get('nasajon_mda.ns_anexosmodulos_repository')->buscaArquivoNotaPorTipo($entity['id_conjunto_anexo'], $tipo);

            return new Response(stream_get_contents($response), Response::HTTP_OK, array('content-type' => $contentType, 'Content-Disposition' => 'attachment'));
        } catch (NoResultException $e) {
            throw $this->createNotFoundException('Unable to find.', $e);
        }
    }

}
