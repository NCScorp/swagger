<?php

namespace Nasajon\AppBundle\DTO\Financas;

class ItemRpsDTO
{
    protected $itemcontratoServico;
    protected $itemcontratoCodigo;
    protected $itemcontratoValor;
    protected $itemcontratoQuantidade;
    protected $itemcontratoCreatedAt;
    protected $respfinValorpagar;
    protected $composicaoNome;
    protected $composicaoServicotecnico;
    protected $familiaCodigo;
    protected $familiaDescricao;
    protected $cfopCfop;
    protected $cfopDescricao;
    protected $contratoDesconto;

    public function fillDTO($arrDados){
        $this->itemcontratoServico = isset($arrDados['itemcontrato_servico']) ? $arrDados['itemcontrato_servico'] : '';
        $this->itemcontratoCodigo = isset($arrDados['itemcontrato_codigo']) ? $arrDados['itemcontrato_codigo'] : '';
        $this->itemcontratoValor = isset($arrDados['itemcontrato_valor']) ? $arrDados['itemcontrato_valor'] : '';
        $this->itemcontratoQuantidade = isset($arrDados['itemcontrato_quantidade']) ? $arrDados['itemcontrato_quantidade'] : '';
        $this->itemcontratoCreatedAt = isset($arrDados['itemcontrato_created_at']) ? $arrDados['itemcontrato_created_at'] : '';
        $this->respfinValorpagar = isset($arrDados['respfin_valorpagar']) ? $arrDados['respfin_valorpagar'] : '';
        $this->composicaoNome = isset($arrDados['composicao_nome']) ? $arrDados['composicao_nome'] : '';
        $this->composicaoServicotecnico = isset($arrDados['composicao_servicotecnico']) ? $arrDados['composicao_servicotecnico'] : '';
        $this->familiaCodigo = isset($arrDados['familia_codigo']) ? $arrDados['familia_codigo'] : '';
        $this->familiaDescricao = isset($arrDados['familia_descricao']) ? $arrDados['familia_descricao'] : '';
        $this->cfopCfop = isset($arrDados['cfop_cfop']) ? $arrDados['cfop_cfop'] : '';
        $this->cfopDescricao = isset($arrDados['cfop_descricao']) ? $arrDados['cfop_descricao'] : '';
        $this->contratoDesconto = isset($arrDados['contrato_desconto']) ? $arrDados['contrato_desconto'] : '';
    }

    /**
     * Get the value of contratoDesconto
     */ 
    public function getContratoDesconto()
    {
        return $this->orcamentoValor;
    }

    /**
     * Set the value of contratoDesconto
     *
     * @return  self
     */ 
    public function setContratoDesconto($contratoDesconto)
    {
        $this->contratoDesconto = $contratoDesconto;

        return $this;
    }

    /**
     * Get the value of itemcontratoServico
     */ 
    public function getItemcontratoServico()
    {
        return $this->itemcontratoServico;
    }

    /**
     * Set the value of itemcontratoServico
     *
     * @return  self
     */ 
    public function setItemcontratoServico($itemcontratoServico)
    {
        $this->itemcontratoServico = $itemcontratoServico;

        return $this;
    }

    /**
     * Get the value of itemcontratoCodigo
     */ 
    public function getItemcontratoCodigo()
    {
        return $this->itemcontratoCodigo;
    }

    /**
     * Set the value of itemcontratoCodigo
     *
     * @return  self
     */ 
    public function setItemcontratoCodigo($itemcontratoCodigo)
    {
        $this->itemcontratoCodigo = $itemcontratoCodigo;

        return $this;
    }

    /**
     * Get the value of itemcontratoValor
     */ 
    public function getItemcontratoValor()
    {
        return $this->itemcontratoValor;
    }

    /**
     * Set the value of itemcontratoValor
     *
     * @return  self
     */ 
    public function setItemcontratoValor($itemcontratoValor)
    {
        $this->itemcontratoValor = $itemcontratoValor;

        return $this;
    }

    /**
     * Get the value of itemcontratoQuantidade
     */ 
    public function getItemcontratoQuantidade()
    {
        return $this->itemcontratoQuantidade;
    }

    /**
     * Set the value of itemcontratoQuantidade
     *
     * @return  self
     */ 
    public function setItemcontratoQuantidade($itemcontratoQuantidade)
    {
        $this->itemcontratoQuantidade = $itemcontratoQuantidade;

        return $this;
    }

    /**
     * Get the value of itemcontratoCreatedAt
     */ 
    public function getItemcontratoCreatedAt()
    {
        return $this->itemcontratoCreatedAt;
    }

    /**
     * Set the value of itemcontratoCreatedAt
     *
     * @return  self
     */ 
    public function setItemcontratoCreatedAt($itemcontratoCreatedAt)
    {
        $this->itemcontratoCreatedAt = $itemcontratoCreatedAt;

        return $this;
    }

    /**
     * Get the value of respfinValorpagar
     */ 
    public function getRespfinValorpagar()
    {
        return $this->respfinValorpagar;
    }

    /**
     * Set the value of respfinValorpagar
     *
     * @return  self
     */ 
    public function setRespfinValorpagar($respfinValorpagar)
    {
        $this->respfinValorpagar = $respfinValorpagar;

        return $this;
    }

    /**
     * Get the value of composicaoNome
     */ 
    public function getComposicaoNome()
    {
        return $this->composicaoNome;
    }

    /**
     * Set the value of composicaoNome
     *
     * @return  self
     */ 
    public function setComposicaoNome($composicaoNome)
    {
        $this->composicaoNome = $composicaoNome;

        return $this;
    }

    /**
     * Get the value of composicaoServicotecnico
     */ 
    public function getComposicaoServicotecnico()
    {
        return $this->composicaoServicotecnico;
    }

    /**
     * Set the value of composicaoServicotecnico
     *
     * @return  self
     */ 
    public function setComposicaoServicotecnico($composicaoServicotecnico)
    {
        $this->composicaoServicotecnico = $composicaoServicotecnico;

        return $this;
    }

    /**
     * Get the value of familiaCodigo
     */ 
    public function getFamiliaCodigo()
    {
        return $this->familiaCodigo;
    }

    /**
     * Set the value of familiaCodigo
     *
     * @return  self
     */ 
    public function setFamiliaCodigo($familiaCodigo)
    {
        $this->familiaCodigo = $familiaCodigo;

        return $this;
    }

    /**
     * Get the value of familiaDescricao
     */ 
    public function getFamiliaDescricao()
    {
        return $this->familiaDescricao;
    }

    /**
     * Set the value of familiaDescricao
     *
     * @return  self
     */ 
    public function setFamiliaDescricao($familiaDescricao)
    {
        $this->familiaDescricao = $familiaDescricao;

        return $this;
    }

    /**
     * Get the value of cfopCfop
     */ 
    public function getCfopCfop()
    {
        return $this->cfopCfop;
    }

    /**
     * Set the value of cfopCfop
     *
     * @return  self
     */ 
    public function setCfopCfop($cfopCfop)
    {
        $this->cfopCfop = $cfopCfop;

        return $this;
    }

    /**
     * Get the value of cfopDescricao
     */ 
    public function getCfopDescricao()
    {
        return $this->cfopDescricao;
    }

    /**
     * Set the value of cfopCfop
     *
     * @return  self
     */ 
    public function setCfopDescricao($cfopDescricao)
    {
        $this->cfopDescricao = $cfopDescricao;

        return $this;
    }
}
