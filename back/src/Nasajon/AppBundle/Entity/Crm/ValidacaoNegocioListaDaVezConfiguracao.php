<?php

namespace Nasajon\AppBundle\Entity\Crm;

class ValidacaoNegocioListaDaVezConfiguracao
{
    /**
     * @var bool
     */
    private $possuiconfiguracao = true;
    /**
     * @var bool
     */
    private $valido = false;
    /**
     * @var bool
     */
    private $vendedorfixo = false;
    /**
     * @var \Nasajon\MDABundle\Entity\Crm\Listadavezvendedores
     */
    private $listadavezvendedor;

    /**
     * Constructor
     */
    public function __construct(){}

    /**
     * Set possuiconfiguracao
     */
    public function setPossuiconfiguracao($possuiconfiguracao)
    {
        $this->possuiconfiguracao = $possuiconfiguracao;
    }
    /**
     * Get possuiconfiguracao
     */
    public function getPossuiconfiguracao()
    {
        return $this->possuiconfiguracao;
    }
    /**
     * Set valido
     */
    public function setValido($valido)
    {
        $this->valido = $valido;
    }
    /**
     * Get valido
     */
    public function getValido()
    {
        return $this->valido;
    }
    /**
     * Set vendedorfixo
     */
    public function setVendedorfixo($vendedorfixo)
    {
        $this->vendedorfixo = $vendedorfixo;
    }
    /**
     * Get vendedorfixo
     */
    public function getVendedorfixo()
    {
        return $this->vendedorfixo;
    }
    /**
     * Set listadavezvendedor
     */
    public function setListadavezvendedor($listadavezvendedor)
    {
        $this->listadavezvendedor = $listadavezvendedor;
    }
    /**
     * Get listadavezvendedor
     */
    public function getListadavezvendedor()
    {
        return $this->listadavezvendedor;
    }
}
