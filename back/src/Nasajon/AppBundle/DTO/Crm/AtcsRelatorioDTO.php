<?php

namespace Nasajon\AppBundle\DTO\Crm;

class AtcsRelatorioDTO
{
    protected $atcs;
    protected $prestador;
    protected $seguros;
    protected $servicosorcados;
    protected $documentos;
    protected $rps;
    protected $fornecedoresenvolvidos;

    public function setAtcs($atcs)
    {
        $this->atcs = $atcs;
    }

    public function getAtcs()
    {
        return $this->atcs;
    }

    public function setPrestador($prestador)
    {
        $this->prestador = $prestador;
    }

    public function getPrestador()
    {
        return $this->prestador;
    }

    public function getSeguros()
    {
        return $this->seguros;
    }

    public function addSeguro($seguro)
    {
        $this->seguros[] = $seguro;
    }

    public function getServicosorcados()
    {
        return $this->servicosorcados;
    }

    public function addServicosorcados($servicoorcado)
    {
        $this->servicosorcados[] = $servicoorcado;
    }

    public function getDocumentos()
    {
        return $this->documentos;
    }

    public function addDocumentos($documento)
    {
        $this->documentos[] = $documento;
    }
    


    /**
     * Get the value of rps
     */ 
    public function getRps()
    {
        return $this->rps;
    }

    /**
     * Set the value of rps
     *
     * @return  self
     */ 
    public function setRps($rps)
    {
        $this->rps = $rps;

        return $this;
    }


    public function getFornecedoresenvolvidos()
    {
        return $this->fornecedoresenvolvidos;
    }

    public function addFornecedoresenvolvidos($fornecedorEnvolvido)
    {
        $this->fornecedoresenvolvidos[] = $fornecedorEnvolvido;
    }
}
