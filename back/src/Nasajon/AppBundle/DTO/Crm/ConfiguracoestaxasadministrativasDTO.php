<?php

namespace Nasajon\AppBundle\DTO\Crm;

class ConfiguracoestaxasadministrativasDTO
{
    protected $valor;
    protected $seguradoraRazaosocial;
    protected $seguradoraNomefantasia;
    protected $itemfaturamentoDescricaoservico;
    protected $formapagamentoCodigo;
    protected $formapagamentoDescricao;
    protected $municipio;

    public function fillDTO($arrDados)
    {
        $this->valor = isset($arrDados['valor']) ? $arrDados['valor'] : '';
        $this->seguradoraRazaosocial = isset($arrDados['seguradora']['razaosocial']) ? $arrDados['seguradora']['razaosocial'] : '';
        $this->seguradoraNomefantasia = isset($arrDados['seguradora']['nomefantasia']) ? $arrDados['seguradora']['nomefantasia'] : '';
        $this->itemfaturamentoDescricaoservico = isset($arrDados['itemfaturamento']['descricaoservico']) ? $arrDados['itemfaturamento']['descricaoservico'] : '';
        $this->formapagamentoCodigo = isset($arrDados['formapagamento']['codigo']) ? $arrDados['formapagamento']['codigo'] : '';
        $this->formapagamentoDescricao = isset($arrDados['formapagamento']['descricao']) ? $arrDados['formapagamento']['descricao'] : '';
        $this->municipio = isset($arrDados['municipioprestacao']['nome']) ? $arrDados['municipioprestacao']['nome'] : '';
    }

    /**
     * Get the value of valor
     */ 
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set the value of valor
     *
     * @return  self
     */ 
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get the value of seguradoraRazaosocial
     */ 
    public function getSeguradoraRazaosocial()
    {
        return $this->seguradoraRazaosocial;
    }

    /**
     * Set the value of seguradoraRazaosocial
     *
     * @return  self
     */ 
    public function setSeguradoraRazaosocial($seguradoraRazaosocial)
    {
        $this->seguradoraRazaosocial = $seguradoraRazaosocial;

        return $this;
    }

    /**
     * Get the value of seguradoraNomefantasia
     */ 
    public function getSeguradoraNomefantasia()
    {
        return $this->seguradoraNomefantasia;
    }

    /**
     * Set the value of seguradoraNomefantasia
     *
     * @return  self
     */ 
    public function setSeguradoraNomefantasia($seguradoraNomefantasia)
    {
        $this->seguradoraNomefantasia = $seguradoraNomefantasia;

        return $this;
    }

    /**
     * Get the value of itemfaturamentoDescricaoservico
     */ 
    public function getItemfaturamentoDescricaoservico()
    {
        return $this->itemfaturamentoDescricaoservico;
    }

    /**
     * Set the value of itemfaturamentoDescricaoservico
     *
     * @return  self
     */ 
    public function setItemfaturamentoDescricaoservico($itemfaturamentoDescricaoservico)
    {
        $this->itemfaturamentoDescricaoservico = $itemfaturamentoDescricaoservico;

        return $this;
    }

    /**
     * Get the value of formapagamentoCodigo
     */ 
    public function getFormapagamentoCodigo()
    {
        return $this->formapagamentoCodigo;
    }

    /**
     * Set the value of formapagamentoCodigo
     *
     * @return  self
     */ 
    public function setFormapagamentoCodigo($formapagamentoCodigo)
    {
        $this->formapagamentoCodigo = $formapagamentoCodigo;

        return $this;
    }

    /**
     * Get the value of formapagamentoDescricao
     */ 
    public function getFormapagamentoDescricao()
    {
        return $this->formapagamentoDescricao;
    }

    /**
     * Set the value of formapagamentoDescricao
     *
     * @return  self
     */ 
    public function setFormapagamentoDescricao($formapagamentoDescricao)
    {
        $this->formapagamentoDescricao = $formapagamentoDescricao;

        return $this;
    }

    /**
     * Get the value of municipio
     */ 
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * Set the value of municipio
     *
     * @return  self
     */ 
    public function setMunicipio($municipio)
    {
        $this->municipio = $municipio;

        return $this;
    }
}
