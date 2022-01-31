<?php

namespace Nasajon\AppBundle\DTO\Ns;

use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\AppBundle\DTO\Ns\ContatosDTO;

class ClientesDTO extends Clientes
{
    protected $codigo;
    protected $razaosocial;
    protected $nomefantasia;
    protected $cnpjcpf;
    protected $inscricaomunicipal;
    protected $inscricaoestadual;
    protected $anotacoes;
    protected $enderecolocal;
    protected $enderecocobranca;

    protected $contatoPrincipal;
    protected $contatos;

    public function fillDTO($arrDados)
    {
        $this->codigo = isset($arrDados['codigo']) ? $arrDados['codigo'] : '';
        $this->razaosocial = isset($arrDados['razaosocial']) ? $arrDados['razaosocial'] : '';
        $this->nomefantasia = isset($arrDados['nomefantasia']) ? $arrDados['nomefantasia'] : '';
        $this->cnpjcpf = isset($arrDados['cnpjcpf']) ? $arrDados['cnpjcpf'] : '';
        $this->inscricaomunicipal = isset($arrDados['inscricaomunicipal']) ? $arrDados['inscricaomunicipal'] : '';
        $this->inscricaoestadual = isset($arrDados['inscricaoestadual']) ? $arrDados['inscricaoestadual'] : '';
        $this->anotacoes = isset($arrDados['anotacoes']) ? $arrDados['anotacoes'] : '';


        if(isset($arrDados['contatoPrincipal'])){
            $contatoDTO = new ContatosDTO();
            $contatoDTO->fillDTO($arrDados['contatoPrincipal']);
            $this->contatoPrincipal = $contatoDTO;
        }
        

        if(isset($arrDados['contatos'])){
            foreach ($arrDados['contatos'] as $key => $contato) {
                $contatoDTO = new ContatosDTO();
                $contatoDTO->fillDTO($contato);
                $this->contatos[] = $contatoDTO;
            }
        }
        
        if(isset($arrDados['enderecolocal']) ){
            $enderecoDTO = new EnderecoDTO();
            $enderecoDTO->fillDTO($arrDados['enderecolocal'],'enderecolocal');
            $this->enderecolocal = $enderecoDTO;
        }

        if(isset($arrDados['enderecocobranca']) ){
            $enderecoDTO = new EnderecoDTO();
            $enderecoDTO->fillDTO($arrDados['enderecocobranca'],'enderecocobranca');
            $this->enderecocobranca = $enderecoDTO;
        }

    }

    public function getContatos () {
        return $this->contatos;
    }

    public function setContatos ($contatos){
        $this->contatos = $contatos;
    }

    public function getContatoprincipal () {
        return $this->contatoPrincipal;
    }

    public function setContatoprincipal ($contatoPrincipal){
        $this->contatoPrincipal = $contatoPrincipal;
    }

    /**
     * Get the value of codigo
     */ 
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set the value of codigo
     *
     * @return  self
     */ 
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get the value of razaosocial
     */ 
    public function getRazaosocial()
    {
        return $this->razaosocial;
    }

    /**
     * Set the value of razaosocial
     *
     * @return  self
     */ 
    public function setRazaosocial($razaosocial)
    {
        $this->razaosocial = $razaosocial;

        return $this;
    }

    /**
     * Get the value of nomefantasia
     */ 
    public function getNomefantasia()
    {
        return $this->nomefantasia;
    }

    /**
     * Set the value of nomefantasia
     *
     * @return  self
     */ 
    public function setNomefantasia($nomefantasia)
    {
        $this->nomefantasia = $nomefantasia;

        return $this;
    }

    /**
     * Get the value of cnpjcpf
     */ 
    public function getCnpjcpf()
    {
        return $this->cnpjcpf;
    }

    /**
     * Set the value of cnpjcpf
     *
     * @return  self
     */ 
    public function setCnpjcpf($cnpjcpf)
    {
        $this->cnpjcpf = $cnpjcpf;

        return $this;
    }

    /**
     * Get the value of inscricaomunicipal
     */ 
    public function getInscricaomunicipal()
    {
        return $this->inscricaomunicipal;
    }

    /**
     * Set the value of inscricaomunicipal
     *
     * @return  self
     */ 
    public function setInscricaomunicipal($inscricaomunicipal)
    {
        $this->inscricaomunicipal = $inscricaomunicipal;

        return $this;
    }

    /**
     * Get the value of inscricaoestadual
     */ 
    public function getInscricaoestadual()
    {
        return $this->inscricaoestadual;
    }

    /**
     * Set the value of inscricaoestadual
     *
     * @return  self
     */ 
    public function setInscricaoestadual($inscricaoestadual)
    {
        $this->inscricaoestadual = $inscricaoestadual;

        return $this;
    }

    /**
     * Get the value of anotacoes
     */ 
    public function getAnotacoes()
    {
        return $this->anotacoes;
    }

    /**
     * Set the value of anotacoes
     *
     * @return  self
     */ 
    public function setAnotacoes($anotacoes)
    {
        $this->anotacoes = $anotacoes;

        return $this;
    }

    /**
     * Get the value of enderecolocal
     */ 
    public function getEnderecolocal()
    {
        return $this->enderecolocal;
    }

    /**
     * Set the value of enderecolocal
     *
     * @return  self
     */ 
    public function setEnderecolocal($enderecolocal)
    {
        $this->enderecolocal = $enderecolocal;

        return $this;
    }

    /**
     * Get the value of enderecocobranca
     */ 
    public function getEnderecocobranca()
    {
        return $this->enderecocobranca;
    }

    /**
     * Set the value of enderecocobranca
     *
     * @return  self
     */ 
    public function setEnderecocobranca($enderecocobranca)
    {
        $this->enderecocobranca = $enderecocobranca;

        return $this;
    }
}
