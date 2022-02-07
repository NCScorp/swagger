<?php

namespace AppBundle\Util;



class CpfCpnjUtil {

    static function formatar_cpf_cnpj($doc) {
        $doc = preg_replace("/[^0-9]/", "", $doc);
        $qtd = strlen($doc);
        if($qtd >= 11) {
            if($qtd === 11 ) {
                $docFormatado = 'CPF: ' . substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
            } else {
                $docFormatado = 'CPNJ: ' . substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, -2);
            }
            return $docFormatado;
        } else {
            return 'Documento inválido';
        }
    }
}
