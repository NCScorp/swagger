<?php

namespace Nasajon\AppBundle\Entity\Crm;

class ResponsabilidadesFinanceirasEmLote
{
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $responsabilidadesfinanceiras;
     
    /**
     * @var \Nasajon\MDABundle\Entity\Crm\Atcs
     */
    private $negocio;

    /**
     * @var int
     */
    private $tenant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->responsabilidadesfinanceiras = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get responsabilidadesfinanceiras
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResponsabilidadesFinanceiras()
    {
        return $this->responsabilidadesfinanceiras;
    }

    /**
     * Set responsabilidadesfinanceiras
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setResponsabilidadesFinanceiras($responsabilidadesfinanceiras)
    {
        $this->responsabilidadesfinanceiras = $responsabilidadesfinanceiras;
    }

    /**
     * Add Responsabilidade financeira
     *
     * @param \Nasajon\MDABundle\Entity\Crm\ResponsabilidadesFinanceiras $responsabilidadefinanceira
     *
     * @return ResponsabilidadesFinanceirasEmLote
     */
    public function addResponsabilidadeFinanceira($responsabilidadefinanceira)
    {
        $this->responsabilidadesfinanceiras[] = $responsabilidadefinanceira;

        return $this;
    }

    /**
     * Set negocio
     */
    public function setNegocio($negocio)
    {
        $this->negocio = $negocio;
    }
    /**
     * Get negocio
     */
    public function getNegocio()
    {
        return $this->negocio;
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
