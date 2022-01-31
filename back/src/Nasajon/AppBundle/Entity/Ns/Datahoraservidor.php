<?php

namespace Nasajon\AppBundle\Entity\Ns;

/**
 * Data e hora atual no servidor no qual o banco de dados está hospedado
 */
class Datahoraservidor{

    /**
     * Undocumented variable
     *
     * @var \DateTime
     */
    private $data;

    /**
     * Undocumented variable
     *
     * @var \DateTime
     */
    private $hora;

    /**
     * Get data
     *
     * @return \DateTime
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * Set data
     *
     * @return \DateTime
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    /**
     * Get hora
     *
     * @return \DateTime
     */
    public function getHora()
    {
        return $this->hora;
    }
    /**
     * Set hora
     *
     * @return \DateTime
     */
    public function setHora($hora)
    {
        $this->hora = $hora;
    }
}
