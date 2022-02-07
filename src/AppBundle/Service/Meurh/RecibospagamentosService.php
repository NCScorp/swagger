<?php

namespace AppBundle\Service\Meurh;

use Gaufrette\Adapter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Meurh\RecibospagamentosService as ServiceParent;
use Nasajon\MDABundle\Repository\Meurh\RecibospagamentosRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Util\ManipuladorZipUtil;
use Nasajon\MDABundle\Request\FilterExpression;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * InformesService
 *
 */
class RecibospagamentosService extends ServiceParent
{
    /**
     *
     * @var RecibospagamentosRepository $repository 
     */
    protected $repository;
    /**
     * 
     * @var Adapter $adapter 
     */
    private $adapter;

     /**
     * @var ParameterBag
     */
    protected $fixedAttributes;

    /**
     * 
     * @param RecibospagamentosRepository $repository
     * @param Adapter $adapter
     */
    public function __construct(RecibospagamentosRepository $repository, Adapter $adapter, ParameterBag $fixedAttributes)
    {
        parent::__construct($repository);
        $this->adapter = $adapter;
        $this->fixedAttributes = $fixedAttributes;
    }

    /**
   * @return array
   */
    public function findAll($tenant, $trabalhador, Filter $filter = null){
      if($filter->getOffset() == 0) {
          $ano = $mes = $calculo = null;
      } else {
          $indexes = $filter->getOffset();
          $calculo = isset($indexes["calculo"]) ? $indexes["calculo"] : null;
          $ano = isset($indexes["ano"]) ? $indexes["ano"] : null;
          $mes = isset($indexes["mes"]) ? $indexes["mes"] : null;
      }

      return $this->getRepository()->findAllOrderAnoEMes($tenant, $trabalhador, $ano, $mes, $calculo);
    }
    
    public function getPdfContent($url, $opcao, $nomeArquivo,$id) {
        $report = $this->adapter->read($url);
        if($report === false) {
            throw new NotFoundHttpException("Arquivo não encontrado.");
        }
        $tenant = $this->fixedAttributes->get('tenant');
        $this->reciboVisualizado($id, $tenant);
        $response = new Response($report);
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $nomeArquivo);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', strlen($report));
        
        if ($opcao == 'baixar') {
            $response->headers->set('Content-Type', 'application/force-download');
        } else {
            $response->headers->set('Content-Type', 'application/pdf');
        }
        
        return $response;
    }
    
    public function getZipPdfsContent($entities, $trabalhador, $cache_dir) {
        $arquivo = [];

        $tenant = $this->fixedAttributes->get('tenant');
        for($i = 0; $i < count($entities); $i++) {
            $filter = new Filter();
            $filter->addToFilterExpression(
                new FilterExpression(
                    "recibopagamento",
                    "eq",
                    $entities[$i]["recibopagamento"]
                )
            );
            $entity = [];
            $entity = $this->find($entities[$i]["recibopagamento"], $tenant, $trabalhador);
            if(!count($entity)) {
                throw new AccessDeniedHttpException();
            }
            $recibos_pdfs[$i] = $this->adapter->read($entity["caminhodocumento"]);
            $this->reciboVisualizado($entities[$i]["recibopagamento"], $tenant);
        }
        
        $arquivo = array_merge(
            $arquivo,
            array_map(
                function($chave, $valor) use ($cache_dir) {
                    return ManipuladorZipUtil::criar(
                        $chave,
                        'pdf',
                        $valor,
                        $cache_dir . '/tmp/arquivos/'
                    );
                },
            array_keys($recibos_pdfs),
            $recibos_pdfs)
        );

        $compactado = ManipuladorZipUtil::compactar('recibos', $arquivo, $cache_dir . '/tmp/zip/', true);

        $response = new Response();
        
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $compactado['nome'] . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Type', 'application/force-download');
        
        
        $response->headers->set('Content-length', $compactado['tamanho']);
        $response->setContent($compactado['arquivo']);

        ManipuladorZipUtil::remover($arquivo);
        ManipuladorZipUtil::remover($compactado['path']);

        return $response;
    }

    public function reciboVisualizado($recibo, $tenant) {
        return $this->getRepository()->reciboVisualizado($recibo, $tenant);
    }
}