<?php

namespace Nasajon\AppBundle\Entity\Crm;

/**
 * Templatespropostascapituloscomposicoes
 */
class TemplatespropostascapituloscomposicoesBulk
{
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $templatescomposicoes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->templatescomposicoes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add Templatespropostascapituloscomposicoes
     *
     * @param \Nasajon\MDABundle\Entity\Crm\TemplatespropostascapituloscomposicoesBulk $templatescomposicoes
     *
     * @return templatecomposicao
     */
    public function addTemplatescomposicoes($templatescomposicoes)
    {
        $this->templatescomposicoes[] = $templatescomposicoes;

        return $this;
    }

    /**
     * Remove Templatespropostascapituloscomposicoes
     *
     * @param \Nasajon\AppBundle\Entity\Crm\TemplatespropostascapituloscomposicoesBulk $templatescomposicoes
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeTemplatescomposicoes($templatescomposicoes)
    {
        return $this->templatescomposicoes->removeElement($templatescomposicoes);
    }

    /**
     * Get templatescomposicoes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTemplatescomposicoes()
    {
        return $this->templatescomposicoes;
    }

    /**
     * Set templatescomposicoes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setTemplatescomposicoes($templatescomposicoes)
    {
        $this->templatescomposicoes = $templatescomposicoes;
    }
}
