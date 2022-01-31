<?php

namespace Nasajon\AppBundle\DTO\Crm;

class DocumentoDTO
{
    protected $nome;
    protected $datarecebimento;
    protected $copiaautenticada;
    protected $copiasimples;
    protected $original;

    public function fillDTO($arrDados)
    {
        $this->nome = isset($arrDados['nome']) ? $arrDados['nome'] : '';
        $this->datarecebimento = isset($arrDados['datarecebimento']) ? $arrDados['datarecebimento'] : '';
        $this->copiaautenticada = isset($arrDados['copiaautenticada']) ? $arrDados['copiaautenticada'] : '';
        $this->copiasimples = isset($arrDados['copiasimples']) ? $arrDados['copiasimples'] : '';
        $this->original = isset($arrDados['original']) ? $arrDados['original'] : '';
    }

    public function geNome () {
        return $this->nome;
    }
    public function getDatarecebimento () {
        return $this->datarecebimento;
    }
    public function getCopiaautenticada () {
        return $this->copiaautenticada;
    }
    public function getCopiasimples () {
        return $this->copiasimples;
    }
    public function getOriginal () {
        return $this->original;
    }

    public function SeNome ($nome) {
        $this->nome = $nome;
    }
    public function setDatarecebimento ($datarecebimento) {
        $this->datarecebimento = $datarecebimento;
    }
    public function setCopiaautenticada ($copiaautenticada) {
        $this->copiaautenticada = $copiaautenticada;
    }
    public function setCopiasimples ($copiasimples) {
        $this->copiasimples = $copiasimples;
    }
    public function setOriginal ($original) {
        $this->original = $original;
    }
}
