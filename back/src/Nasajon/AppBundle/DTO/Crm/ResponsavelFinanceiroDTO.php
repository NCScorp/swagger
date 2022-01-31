<?php

namespace Nasajon\AppBundle\DTO\Crm;

use Nasajon\MDABundle\Entity\Crm\Atcsresponsaveisfinanceiros;

class ResponsavelFinanceiroDTO extends Atcsresponsaveisfinanceiros
{

    protected $nome;
    protected $principal;

    public function fillDTO($arrDados)
    {
        $this->principal = isset($arrDados['principal']) ? $arrDados['principal'] : '';
        $this->nome = isset($arrDados['nome']) ? $arrDados['nome'] : '';
    }

    public function getPrincipal () {
        return $this->principal;
    }
    public function setPrincipal ($principal) {
        $this->principal = $principal;
    }

    public function getNome () {
        return $this->nome;
    }
    public function setNome ($nome) {
        $this->nome = $nome;
    }

}
