<?php

namespace Nasajon\AppBundle\DTO\Ns;

class TelefonesDTO
{
    private $ddi;
    private $ddd;
    private $telefone;
    private $ramal;
    private $principal;
    private $observacao;

    public function fillDTO($arrDados)
    {
        $this->ddi = isset($arrDados['ddi']) ? $arrDados['ddi'] : '';
        $this->ddd = isset($arrDados['ddd']) ? $arrDados['ddd'] : '';
        $this->telefone = isset($arrDados['telefone']) ? $arrDados['telefone'] : '';
        $this->ramal = isset($arrDados['ramal']) ? $arrDados['ramal'] : '';
        $this->principal = isset($arrDados['principal']) ? $arrDados['principal'] : '';
        $this->observacao = isset($arrDados['observacao']) ? $arrDados['observacao'] : '';
    }

    public function getDdi () {
        return $this->ddi;
    }
    public function getDdd () {
        return $this->ddd;
    }
    public function getTelefone () {
        return $this->telefone;
    }
    public function getRamal () {
        return $this->ramal;
    }
    public function getPrincipal () {
        return $this->principal;
    }
    public function getObservacao () {
        return $this->observacao;
    }

    public function setDdi ($ddi) {
        $this->ddi = $ddi;
    }
    public function setDdd ($ddd) {
        $this->ddd = $ddd;
    }
    public function setTelefone ($telefone) {
        $this->telefone = $telefone;
    }
    public function setRamal ($ramal) {
        $this->ramal = $ramal;
    }
    public function setPrincipal ($principal) {
        $this->principal = $principal;
    }
    public function setObservacao ($observacao) {
        $this->observacao = $observacao;
    }


}
