<?php

namespace Nasajon\AppBundle\DTO\Ns;

use Nasajon\AppBundle\DTO\Ns\EnderecoDTO;
use Nasajon\MDABundle\Entity\Ns\Estabelecimentos;
use Nasajon\AppBundle\DTO\Crm\ConfiguracoestaxasadministrativasDTO;

class EstabelecimentoDTO extends Estabelecimentos
{

    private $razaosocial;
    private $nomefantasia;
    private $cnpjcpf;
    private $inscricaoestadual;
    private $inscricaomunicipal;
    private $telefone;
    private $pathlogo;
    private $email;

    private $endereco;

    private $configuracaoTaxaAdministrativa;
    
    public function fillDTO($arrDados)
    {
        $this->razaosocial = isset($arrDados['razaosocial']) ? $arrDados['razaosocial'] : '';
        $this->nomefantasia = isset($arrDados['nomefantasia']) ? $arrDados['nomefantasia'] : '';
        $this->inscricaoestadual = isset($arrDados['inscricaoestadual']) ? $arrDados['inscricaoestadual'] : '';
        $this->inscricaomunicipal = isset($arrDados['inscricaomunicipal']) ? $arrDados['inscricaomunicipal'] : '';
        $this->cnpjcpf = isset($arrDados['cnpjcpf']) ? $arrDados['cnpjcpf'] : '';
        $this->telefone = isset($arrDados['telefone']) ? $arrDados['telefone'] : '';
        $this->pathlogo = isset($arrDados['pathlogo']) ? $arrDados['pathlogo'] : '';
        $this->email = isset($arrDados['email']) ? $arrDados['email'] : '';

        $this->endereco = new EnderecoDTO();
        isset($arrDados['endereco']) ? $this->endereco->fillDTO($arrDados['endereco']) : null;
    }

    public function getRazaosocial () {
        return $this->razaosocial;
    }
    public function getNomefantasia () {
        return $this->nomefantasia;
    }
    public function getCnpjcpf () {
        return $this->cnpjcpf;
    }
    public function getInscricaoEstadual () {
        return $this->inscricaoestadual;
    }
    public function getInscricaoMunicipal () {
        return $this->inscricaomunicipal;
    }
    public function getTelefone () {
        return $this->telefone;
    }
    public function getPathlogo () {
        return $this->pathlogo;
    }
    public function getEndereco () {
        return $this->endereco;
    }
    public function getEmail () {
        return $this->email;
    }

    public function setRazaosocial ($razaosocial) {
        $this->razaosocial = $razaosocial;
    }
    public function setNomefantasia ($nomefantasia) {
        $this->nomefantasia = $nomefantasia;
    }
    public function setInscricaoEstadual ($inscricaoestadual) {
        $this->inscricaoestadual = $inscricaoestadual;
    }
    public function setInscricaoMunicipal ($inscricaomunicipal) {
        $this->inscricaomunicipal = $inscricaomunicipal;
    }
    public function setCnpjcpf ($cnpjcpf) {
        $this->cnpjcpf = $cnpjcpf;
    }
    public function setTelefone ($telefone) {
        $this->telefone = $telefone;
    }
    public function setPathlogo ($pathlogo) {
        $this->pathlogo = $pathlogo;
    }
    public function setEndereco ($endereco) {
        $this->endereco = $endereco;
    }
    public function setEmail ($email) {
        $this->email = $email;
    }

    /**
     * Get the value of configuracaoTaxaAdministrativa
     */ 
    public function getConfiguracaoTaxaAdministrativa(): ConfiguracoestaxasadministrativasDTO
    {
        return $this->configuracaoTaxaAdministrativa;
    }

    /**
     * Set the value of configuracaoTaxaAdministrativa
     *
     * @return  self
     */ 
    public function setConfiguracaoTaxaAdministrativa(ConfiguracoestaxasadministrativasDTO $configuracaoTaxaAdministrativa)
    {
        $this->configuracaoTaxaAdministrativa = $configuracaoTaxaAdministrativa;

        return $this;
    }
}
