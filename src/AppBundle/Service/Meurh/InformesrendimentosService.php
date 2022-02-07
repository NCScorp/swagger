<?php

namespace AppBundle\Service\Meurh;

use Gaufrette\Adapter;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Service\Meurh\InformesrendimentosService as ServiceParent;
use Nasajon\MDABundle\Repository\Meurh\InformesrendimentosRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Util\ManipuladorZipUtil;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * InformesrendimentosService
 *
 */
class InformesrendimentosService extends ServiceParent{
    /**
     *
     * @var InformesrendimentosRepository $repository 
     */
    protected $repository;
    /**
     * 
     * @var Adapter $adapter 
     */
    private $adapter;
    /**
     * 
     * @param InformesrendimentosRepository $repository
     * @param Adapter $adapter
     */
    public function __construct(InformesrendimentosRepository $repository, Adapter $adapter, ParameterBag $fixedAttributes)
    {
        parent::__construct($repository);
        $this->adapter = $adapter;
        $this->fixedAttributes = $fixedAttributes;
    }
    
    public function getPdfContent($url, $opcao, $nomeArquivo,$id) {
        $report = $this->adapter->read($url);
        if($report === false) {
            throw new NotFoundHttpException("Arquivo não encontrado.");
        }
        $tenant = $this->fixedAttributes->get('tenant');
        $this->informeVisualizado($id, $tenant);

        
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
    
    public function getZipPdfsContent($entities, $cache_dir) {
        $arquivo = [];
        $tenant = $this->fixedAttributes->get('tenant');

        for($i = 0; $i < count($entities); $i++) {
            $informes_pdfs[$i] = $this->adapter->read($entities[$i]["caminhodocumento"]);

            $this->informeVisualizado($entities[$i]["informerendimento"], $tenant);

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
            array_keys($informes_pdfs),
            $informes_pdfs)
        );

        $compactado = ManipuladorZipUtil::compactar('informes', $arquivo, $cache_dir . '/tmp/zip/', true);

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

    public function informeVisualizado($informe, $tenant) {
        return $this->getRepository()->informeVisualizado($informe, $tenant);
    }
}