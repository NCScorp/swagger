<?php

namespace Nasajon\AppBundle\Entity\Crm;

class ListadavezconfiguracoesEmLote
{
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $listadavezconfiguracoes;

    /**
     * @var int
     */
    private $tenant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->listadavezconfiguracoes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get listadavezconfiguracoes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListadavezconfiguracoes()
    {
        return $this->listadavezconfiguracoes;
    }

    /**
     * Set listadavezconfiguracoes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setListadavezconfiguracoes($listadavezconfiguracoes)
    {
        $this->listadavezconfiguracoes = $listadavezconfiguracoes;
    }

    /**
     * Add Lista da vez configuracao
     *
     * @param \Nasajon\MDABundle\Entity\Crm\Listadavezconfiguracoes $listadavezconfiguracao
     *
     * @return ListadavezconfiguracoesEmLote
     */
    public function addListadavezconfiguracao($listadavezconfiguracao)
    {
        $this->listadavezconfiguracoes[] = $listadavezconfiguracao;

        return $this;
    }

    /**
     * Set tenant
     *
     * @param int $tenant
     */
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Get tenant
     *
     * @return int
     */
    public function getTenant()
    {
        return $this->tenant;
    }
}
