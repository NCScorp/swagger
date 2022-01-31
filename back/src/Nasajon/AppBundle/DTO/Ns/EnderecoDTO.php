<?php

namespace Nasajon\AppBundle\DTO\Ns;

use Nasajon\MDABundle\Entity\Ns\Enderecos;

class EnderecoDTO extends Enderecos
{

    private $tipologradouro;
    private $logradouro;
    private $numero;
    private $complemento;
    private $cep;
    private $bairro;
    private $uf;
    private $paisnome;
    private $municipionome;
    private $enderecocompleto;
    private $referencia;
    private $nome;
    private $tipoEndereco;

    public function fillDTO($arrDados, $tipoEndereco = null)
    {
        // $this->endereco = isset($arrDados) ? $arrDados : '';
        $this->tipologradouro = isset($arrDados['tipologradouro']) ? $arrDados['tipologradouro'] : '';
        $this->logradouro = isset($arrDados['logradouro']) ? $arrDados['logradouro'] : '';
        $this->numero = isset($arrDados['numero']) ? $arrDados['numero'] : '';
        $this->complemento = isset($arrDados['complemento']) ? $arrDados['complemento'] : '';
        $this->cep = isset($arrDados['cep']) ? $arrDados['cep'] : '';
        $this->bairro = isset($arrDados['bairro']) ? $arrDados['bairro'] : '';
        $this->uf = isset($arrDados['uf']) ? $arrDados['uf'] : '';
        $this->paisnome = isset($arrDados['paisnome']) ? $arrDados['paisnome'] : '';
        $this->municipionome = isset($arrDados['municipionome']) ? $arrDados['municipionome'] : '';
        $this->enderecocompleto = isset($arrDados['enderecocompleto']) ? $arrDados['enderecocompleto'] : '';
        $this->referencia = isset($arrDados['referencia']) ? $arrDados['referencia'] : '';
        $this->nome = isset($arrDados['nome']) ? $arrDados['nome'] : '';
        $this->tipoEndereco = $tipoEndereco;
    }

    public function getTipologradouro () {
        return $this->tipologradouro;
    }
    public function getLogradouro () {
        return $this->logradouro;
    }
    public function getNumero () {
        return $this->numero;
    }
    public function getComplemento () {
        return $this->complemento;
    }
    public function getCep () {
        return $this->cep;
    }
    public function getBairro () {
        return $this->bairro;
    }
    public function getUf () {
        return $this->uf;
    }
    public function getPaisnome () {
        return $this->paisnome;
    }
    public function getMunicipionome () {
        return $this->municipionome;
    }
    public function getEnderecocompleto () {
        return $this->enderecocompleto;
    }
    public function getReferencia () {
        return $this->referencia;
    }
    public function getNome () {
        return $this->nome;
    }
    public function getTipoEndereco () {
        return $this->tipoEndereco;
    }
    


    public function setTipologradouro ($tipologradouro = null) {
        $this->tipologradouro = $tipologradouro;
    }
    public function setLogradouro ($logradouro) {
        $this->logradouro = $logradouro;
    }
    public function setNumero ($numero) {
        $this->numero = $numero;
    }
    public function setComplemento ($complemento) {
        $this->complemento = $complemento;
    }
    public function setCep ($cep) {
        $this->cep = $cep;
    }
    public function setBairro ($bairro) {
        $this->bairro = $bairro;
    }
    public function setUf ($uf) {
        $this->uf = $uf;
    }
    public function setPaisnome ($paisnome) {
        $this->paisnome = $paisnome;
    }
    public function setMunicipionome ($municipionome) {
        $this->municipionome = $municipionome;
    }
    public function setEnderecocompleto ($enderecocompleto) {
        $this->enderecocompleto = $enderecocompleto;
    }
    public function setReferencia ($referencia) {
        $this->referencia = $referencia;
    }
    public function setNome ($nome) {
        $this->nome = $nome;
    }
    public function setTipoEndereco ($tipoEndereco) {
        $this->tipoEndereco = $tipoEndereco;
    }
    
}
