<?php

namespace Nasajon\AppBundle\Entity\Crm;

/**
 * OrcamentosBulk
 */
class OrcamentosBulk
{
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $orcamentos;
    private $negocio;
    private $proposta;
    private $propostaitem;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orcamentos = new \Doctrine\Common\Collections\ArrayCollection();
    }



    /**
     * Add Orcamentos
     *
     * @param \Nasajon\MDABundle\Entity\Crm\OrcamentosBulk $orcamentos
     *
     * @return Orcamento
     */
    public function addOrcamentos($orcamentos)
    {
        $this->orcamentos[] = $orcamentos;

        return $this;
    }

    /**
     * Remove Orcamentos
     *
     * @param \Nasajon\AppBundle\Entity\Crm\OrcamentosBulk $orcamentos
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOrcamentos($orcamentos)
    {
        return $this->orcamentos->removeElement($orcamentos);
    }

    /**
     * Get orcamentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrcamentos()
    {
        return $this->orcamentos;
    }

    /**
     * Set orcamentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setOrcamentos($orcamentos)
    {
        $this->orcamentos = $orcamentos;
    }

    /**
     * Get negocio
     *
     * @return string
     */
    public function getNegocio()
    {
        return $this->negocio;
    }

    /**
     * Set negocio
     */
    public function setNegocio($negocio)
    {
        $this->negocio = $negocio;
    }

    /**
     * Get proposta
     *
     * @return string
     */
    public function getProposta()
    {
        return $this->proposta;
    }

    /**
     * Set proposta
     *
     * @return string
     */
    public function setProposta($proposta)
    {
        $this->proposta = $proposta;
    }

    /**
     * Get propostaitem
     *
     * @return string
     */
    public function getPropostaitem()
    {
        return $this->propostaitem;
    }
    /**
     * Set propostaitem
     *
     * @return string
     */
    public function setPropostaitem($propostaitem)
    {
        $this->propostaitem = $propostaitem;
    }
}
