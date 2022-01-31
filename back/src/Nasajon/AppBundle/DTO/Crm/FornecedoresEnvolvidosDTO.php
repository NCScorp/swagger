<?php

namespace Nasajon\AppBundle\DTO\Crm;

use Nasajon\AppBundle\DTO\Ns\FornecedoresDTO;
use Nasajon\MDABundle\Entity\Crm\Fornecedoresenvolvidos;

class FornecedoresEnvolvidosDTO extends Fornecedoresenvolvidos
{
    protected $nome;
    protected $descontoglobal;

    protected $fornecedor;

    public static function newFromArray($arrDados)
    {
        $dto = new self;
        $dto->nome = isset($arrDados['nome']) ? $arrDados['nome'] : '';
        $dto->descontoglobal = isset($arrDados['descontoglobal']) ? $arrDados['descontoglobal'] : '';
        $dto->fornecedor = FornecedoresDTO::newFromArray($arrDados['fornecedor']);
        return $dto;
    }

    /**
     * Get the value of nome
     */ 
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set the value of nome
     *
     * @return  self
     */ 
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get the value of descontoglobal
     */ 
    public function getDescontoglobal()
    {
        return $this->descontoglobal;
    }

    /**
     * Set the value of descontoglobal
     *
     * @return  self
     */ 
    public function setDescontoglobal($descontoglobal)
    {
        $this->descontoglobal = $descontoglobal;

        return $this;
    }
}
