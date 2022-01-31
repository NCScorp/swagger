<?php

namespace Nasajon\AppBundle\Service\Common;

class DocumentosFopService
{

    protected $diretorioClient;

    public function __construct(
        $diretorioClient
    ) {
        $this->diretorioClient = $diretorioClient;
    }
    
    public function getDocumentoFop($stringXML, $nomeDocumento, $tenant, $id_grupoempresarial){

        $dados = [
            "codigodocumento" => $nomeDocumento,
            "fontedadosxml" => $stringXML,
            "tenant" => $tenant,
            "grupoempresarial" => $id_grupoempresarial,
            // "estabelecimento" => $estabelecimento,
            // "empresa" => $empresa,
        ];
        $dadosPDF = $this->diretorioClient->postGeraDocumentoFop($dados, false);
        return $dadosPDF;
    }

}
