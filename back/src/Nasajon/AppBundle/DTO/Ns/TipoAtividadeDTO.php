<?php

namespace Nasajon\AppBundle\DTO\Ns;

class TipoAtividadeDTO
{
    private $nome;
    private $descricao;

    public function fillDTO($arrDados)
    {
        $this->nome = isset($arrDados['nome']) ? $arrDados['nome'] : '';
        $this->descricao = isset($arrDados['descricao']) ? $arrDados['descricao'] : '';
    }

    public function getNome () {
        return $this->nome;
    }
    public function getDescricao () {
        return $this->descricao;
    }

    public function setDescricao ($descricao) {
        $this->descricao = $descricao;
    }
    public function setNome ($nome) {
        $this->nome = $nome;
    }
}
