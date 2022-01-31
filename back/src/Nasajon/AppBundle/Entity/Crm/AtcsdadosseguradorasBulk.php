<?php

namespace Nasajon\AppBundle\Entity\Crm;

/**
 * Negociosdadosseguradoras
 */
class AtcsdadosseguradorasBulk
{
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $negociosdadosseguradoras;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->negociosdadosseguradoras = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add Negociosdadosseguradoras
     *
     * @param \Nasajon\MDABundle\Entity\Crm\AtcsdadosseguradorasBulk $negociosdadosseguradoras
     *
     * @return AtcsdadosseguradorasBulk
     */
    public function addNegociosdadosseguradoras($negociosdadosseguradoras)
    {
        $this->negociosdadosseguradoras[] = $negociosdadosseguradoras;

        return $this;
    }

    /**
     * Remove Negociosdadosseguradoras
     *
     * @param \Nasajon\AppBundle\Entity\Crm\NegociosdadosseguradorasBulk $negociosdadosseguradoras
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNegociosdadosseguradoras($negociosdadosseguradoras)
    {
        return $this->negociosdadosseguradoras->removeElement($negociosdadosseguradoras);
    }

    /**
     * Get negociosdadosseguradoras
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNegociosdadosseguradoras()
    {
        return $this->negociosdadosseguradoras;
    }

    /**
     * Set negociosdadosseguradoras
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setNegociosdadosseguradoras($negociosdadosseguradoras)
    {
        $this->negociosdadosseguradoras = $negociosdadosseguradoras;
    }
}
