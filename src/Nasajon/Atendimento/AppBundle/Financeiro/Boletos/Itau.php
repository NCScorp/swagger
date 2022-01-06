<?php

namespace Nasajon\Atendimento\AppBundle\Financeiro\Boletos;

use Nasajon\Atendimento\AppBundle\Financeiro\Boletos;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos;

class Itau implements Boletos {

    /**
     * {@inheritdoc}
     */
    public function gerar(Titulos $titulo) {

        $cripto = new Itaucripto();

        $codSacado = $titulo->getCliente()->getCnpj();
        
        $codEmp = $titulo->getChaveseguranca()['codigo'];
        $chave = $titulo->getChaveseguranca()['chave'];

        $dados = $cripto->geraCripto($codEmp, $codSacado, $chave);
        if(substr($dados, 0, 5) == "Erro:"){
            throw new \Exception(substr($dados, 6));
        }
      
        $html = "<html>
    <head>
    <title> Segunda Via de Bloquetos </title>
    <script src=\"https://code.jquery.com/jquery-3.1.1.min.js\" integrity=\"sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=\" crossorigin=\"anonymous\"></script>        
    </head>                    
    <body bgcolor=\"white\">
    <FORM METHOD=\"POST\" ACTION=\"https://ww2.itau.com.br/2viabloq/pesquisa.asp\" id=\"segunda_via\" name=\"form\">
    <INPUT TYPE=\"hidden\" NAME=\"DC\" VALUE=\"" . $dados . "\">
    <INPUT TYPE=\"hidden\" NAME=\"msg\" VALUE=\"S\">
    </FORM>
    <script language=\"javascript\">
    <!--
    $(document).ready(function() { $('#segunda_via').submit(); });
    //-->
    </script>
    </body>
</html>";

        return new \Symfony\Component\HttpFoundation\Response($html, \Symfony\Component\HttpFoundation\Response::HTTP_OK, array('content-type' => 'text/html'));
        ///
    }

}
