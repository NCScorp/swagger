<?php

namespace AppBundle\Enum;

// Enum para ser usado para converter nomes de campos em suas respectivas labels
abstract class DocumentosObrigatoriosEnum
{
    const documentosObrigatorios = [
        [
            "descricao" => "Comprovante de Residência",
            "obrigatorio" => true,
            "tiposolicitacao" => 5
        ],
        [
            "descricao" => "CPF",
            "obrigatorio" => true,
            "tiposolicitacao" => 0
        ],
        [
            "descricao" => "Comprovante de Residência",
            "obrigatorio" => false,
            "tiposolicitacao" => 0
        ],
        [
            "descricao" => "NIS",
            "obrigatorio" => false,
            "tiposolicitacao" => 0
        ],
        [
            "descricao" => "Carteira de Trabalho",
            "obrigatorio" => false,
            "tiposolicitacao" => 0
        ],
        [
            "descricao" => "RG",
            "obrigatorio" => false,
            "tiposolicitacao" => 0
        ],
        [
            "descricao" => "CNH",
            "obrigatorio" => false,
            "tiposolicitacao" => 0
        ]
    ];
}