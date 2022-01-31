<?php

namespace Nasajon\AppBundle\DTO\Crm;

class SeguroDTO
{
    protected $produto;
    protected $tipoapolice;
    protected $apolice;
    protected $valorautorizado;
    protected $sinistro;
    protected $nomefuncionario;
    protected $titularapolice;
    protected $titularvinculo;

    public function fillDTO($arrDados)
    {
        $this->produto = isset($arrDados['produto']) ? $arrDados['produto'] : '';
        $this->tipoapolice = isset($arrDados['tipoapolice']) ? $arrDados['tipoapolice'] : '';
        $this->apolice = isset($arrDados['apolice']) ? $arrDados['apolice'] : '';
        $this->valorautorizado = isset($arrDados['valorautorizado']) ? $arrDados['valorautorizado'] : '';
        $this->sinistro = isset($arrDados['sinistro']) ? $arrDados['sinistro'] : '';
        $this->nomefuncionario = isset($arrDados['nomefuncionario']) ? $arrDados['nomefuncionario'] : '';
        $this->titularapolice = isset($arrDados['titularapolice']) ? $arrDados['titularapolice'] : '';
        $this->titularvinculo = isset($arrDados['titularvinculo']) ? $arrDados['titularvinculo'] : '';
    }

    public function getProduto () {
        return $this->produto;
    }
    public function getTipoapolice () {
        return $this->tipoapolice;
    }
    public function getApolice () {
        return $this->apolice;
    }
    public function getValorautorizado () {
        return $this->valorautorizado;
    }
    public function getSinistro () {
        return $this->sinistro;
    }
    public function getNomefuncionario () {
        return $this->nomefuncionario;
    }
    public function getTitularapolice () {
        return $this->titularapolice;
    }
    public function getTitularvinculo () {
        return $this->titularvinculo;
    }

    public function setProduto ($produto) {
        $this->produto = $produto;
    }
    public function setTipoapolice ($tipoapolice) {
        $this->tipoapolice = $tipoapolice;
    }
    public function setApolice ($apolice) {
        $this->apolice = $apolice;
    }
    public function setValorautorizado ($valorautorizado) {
        $this->valorautorizado = $valorautorizado;
    }
    public function setSinistro ($sinistro) {
        $this->sinistro = $sinistro;
    }
    public function setNomefuncionario ($nomefuncionario) {
        $this->nomefuncionario = $nomefuncionario;
    }
    public function setTitularapolice ($titularapolice) {
        $this->titularapolice = $titularapolice;
    }
    public function setTitularvinculo ($titularvinculo) {
        $this->titularvinculo = $titularvinculo;
    }

}
