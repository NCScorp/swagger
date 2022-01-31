<?php

namespace Nasajon\AppBundle\DTO\Ns;

use Nasajon\MDABundle\Entity\Ns\Fornecedores;

class FornecedoresDTO extends Fornecedores
{
    protected $fornecedor;
    protected $nomefantasia;
    protected $razaosocial;
    protected $cnpj;
    protected $estabelecimentoid;


    public static function newFromArray($arrDados)
    {
        $dto = new self();
        $dto->fornecedor = isset($arrDados['fornecedor']) ? $arrDados['fornecedor'] : '';
        $dto->razaosocial = isset($arrDados['razaosocial']) ? $arrDados['razaosocial'] : '';
        $dto->nomefantasia = isset($arrDados['nomefantasia']) ? $arrDados['nomefantasia'] : '';
        $dto->cnpj = isset($arrDados['cnpj']) ? $arrDados['cnpj'] : '';
        $dto->estabelecimentoid = isset($arrDados['estabelecimentoid']) ? $arrDados['estabelecimentoid'] : '';
        return $dto;
    }

    /**
     * Get the value of fornecedor
     */ 
    public function getFornecedor()
    {
        return $this->fornecedor;
    }

    /**
     * Set the value of fornecedor
     *
     * @return  self
     */ 
    public function setFornecedor($fornecedor)
    {
        $this->fornecedor = $fornecedor;

        return $this;
    }

    /**
     * Get the value of nomefantasia
     */ 
    public function getNomefantasia()
    {
        return $this->nomefantasia;
    }

    /**
     * Set the value of nomefantasia
     *
     * @return  self
     */ 
    public function setNomefantasia($nomefantasia)
    {
        $this->nomefantasia = $nomefantasia;

        return $this;
    }

    /**
     * Get the value of razaosocial
     */ 
    public function getRazaosocial()
    {
        return $this->razaosocial;
    }

    /**
     * Set the value of razaosocial
     *
     * @return  self
     */ 
    public function setRazaosocial($razaosocial)
    {
        $this->razaosocial = $razaosocial;

        return $this;
    }

    /**
     * Get the value of cnpj
     */ 
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * Set the value of cnpj
     *
     * @return  self
     */ 
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    /**
     * Get the value of estabelecimentoid
     */ 
    public function getEstabelecimentoid()
    {
        return $this->estabelecimentoid;
    }

    /**
     * Set the value of estabelecimentoid
     *
     * @return  self
     */ 
    public function setEstabelecimentoid($estabelecimentoid = null)
    {
        $this->estabelecimentoid = $estabelecimentoid;

        return $this;
    }
}
