<?php

namespace Nasajon\AppBundle\Entity\Crm;

/**
 * Propostas Itens
 */
class PropostasitensBulk
{
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $propostasitens;
     
    /**
     * @var \Nasajon\MDABundle\Entity\Ns\Fornecedores
     */
    private $fornecedor;

    /**
     * @var \Nasajon\MDABundle\Entity\Crm\Propostas
     */
    private $proposta;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->propostasitens = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get propostasitens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPropostasitens()
    {
        return $this->propostasitens;
    }

    /**
     * Set propostasitens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setPropostasitens($propostasitens)
    {
        $this->propostasitens = $propostasitens;
    }

    /**
     * Set fornecedor
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setFornecedor($fornecedor)
    {
        $this->fornecedor = $fornecedor;
    }
    /**
     * Get fornecedor
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFornecedor()
    {
        return $this->fornecedor;
        
    }

    /**
     * Get proposta
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProposta()
    {
        return $this->proposta;
        
    }
}
