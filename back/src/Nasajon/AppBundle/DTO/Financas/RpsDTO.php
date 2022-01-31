<?php

namespace Nasajon\AppBundle\DTO\Financas;

use DateTime;

class RpsDTO
{
    protected $numerorps;
    protected $dataGeracaoRps;
    protected $cfopCfop;
    protected $cfopDescricao;


    protected $itensNota;

    public function fillDTO($arrDados){
        $this->numerorps = isset($arrDados['numerorps']) ? $arrDados['numerorps'] : '';
        $dataGeracaoRps = isset($arrDados['created_at']) ? $arrDados['created_at'] : '';
        $this->setDataGeracaoRps($dataGeracaoRps);
    }

    public function setCfop($itemNota){
        $this->cfopCfop = isset($itemNota['cfop_cfop']) ? $itemNota['cfop_cfop'] : '';
        $this->cfopDescricao = isset($itemNota['cfop_descricao']) ? $itemNota['cfop_descricao'] : '';
    }

    /**
     * Set the value of dataGeracaoRps
     *
     * @return  self
     */ 
    public function setDataGeracaoRps($dataGeracaoRps)
    {
        if($dataGeracaoRps !== '') {
            $dataSemMiliseconds = strstr($dataGeracaoRps,'.',true);
            $datetimeObject = DateTime::createFromFormat('Y-m-d H:i:s',$dataSemMiliseconds);
            if($datetimeObject != false) $dataGeracaoRps = $datetimeObject->format('d/m/Y');
        }
        $this->dataGeracaoRps = $dataGeracaoRps;
        return $this;
    }
    
    /**
     * Get the value of dataGeracaoRps
     */ 
    public function getDataGeracaoRps()
    {
        return $this->dataGeracaoRps;
    }

    public function getItensNota()
    {
        return $this->itensNota;
    }

    public function addItemNota($itemNota)
    {
        $this->itensNota[] = $itemNota;
    }

    /**
     * Get the value of numerorps
     */ 
    public function getNumerorps()
    {
        return $this->numerorps;
    }

    /**
     * Set the value of numerorps
     *
     * @return  self
     */ 
    public function setNumerorps($numerorps)
    {
        $this->numerorps = $numerorps;

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