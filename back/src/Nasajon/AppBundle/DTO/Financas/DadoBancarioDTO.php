<?php

namespace Nasajon\AppBundle\DTO\Financas;

class DadoBancarioDTO
{
    protected $numerobanco;
    protected $nomebanco;
    protected $nomeagencia;
    protected $numeroagencia;
    protected $dvagencia;
    protected $tipoconta;
    protected $numeroconta;
    protected $dvconta;
    protected $contaprincipal;


    public function fillDTO($arrDados){
        $this->numerobanco = isset($arrDados['numerobanco']) ? $arrDados['numerobanco'] : '';
        $this->nomebanco = isset($arrDados['nomebanco']) ? $arrDados['nomebanco'] : '';
        $this->nomeagencia = isset($arrDados['nomeagencia']) ? $arrDados['nomeagencia'] : '';
        $this->numeroagencia = isset($arrDados['numeroagencia']) ? $arrDados['numeroagencia'] : '';
        $this->dvagencia = isset($arrDados['dvagencia']) ? $arrDados['dvagencia'] : '';
        $this->tipoconta = isset($arrDados['tipoconta']) ? $arrDados['tipoconta'] : '';
        $this->numeroconta = isset($arrDados['numeroconta']) ? $arrDados['numeroconta'] : '';
        $this->dvconta = isset($arrDados['dvconta']) ? $arrDados['dvconta'] : '';
        $this->contaprincipal = isset($arrDados['contaprincipal']) ? $arrDados['contaprincipal'] : '';
    }

    public function getNumerobanco () {
        return $this->numerobanco;
    }
    public function getNomebanco () {
        return $this->nomebanco;
    }
    public function getNomeagencia () {
        return $this->nomeagencia;
    }
    public function getNumeroagencia () {
        return $this->numeroagencia;
    }
    public function getDvagencia () {
        return $this->dvagencia;
    }
    public function getTipoconta () {
        return $this->tipoconta;
    }
    public function getNumeroconta () {
        return $this->numeroconta;
    }
    public function getDvconta () {
        return $this->dvconta;
    }
    public function getContaprincipal () {
        return $this->contaprincipal;
    }

    public function setNumerobanco ($numerobanco) {
        $this->numerobanco = $numerobanco;
    }
    public function setNomebanco ($nomebanco) {
        $this->nomebanco = $nomebanco;
    }
    public function setNomeagencia ($nomeagencia) {
        $this->nomeagencia = $nomeagencia;
    }
    public function setNumeroagencia ($numeroagencia) {
        $this->numeroagencia = $numeroagencia;
    }
    public function setDvagencia ($dvagencia) {
        $this->dvagencia = $dvagencia;
    }
    public function setTtipoconta ($tipoconta) {
        $this->tipoconta = $tipoconta;
    }
    public function setNumeroconta ($numeroconta) {
        $this->numeroconta = $numeroconta;
    }
    public function setDvconta ($dvconta) {
        $this->dvconta = $dvconta;
    }
    public function setContaprincipal ($contaprincipal) {
        $this->contaprincipal = $contaprincipal;
    }
}
