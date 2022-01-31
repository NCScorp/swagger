<?php

namespace Nasajon\AppBundle\DTO\Crm;

use Nasajon\AppBundle\DTO\Financas\DadoBancarioDTO;
use Nasajon\AppBundle\DTO\Ns\ContatosDTO;
use Nasajon\AppBundle\DTO\Ns\EnderecoDTO;
use Nasajon\AppBundle\DTO\Ns\TipoAtividadeDTO;

class PrestadorDTO
{

    protected $codigo;
    protected $nomefantasia;
    protected $razaosocial;
    protected $cnpjcpf;
    protected $inscricaomunicipal;
    protected $status;
    protected $anotacoes;
    protected $tiposatividades;
    protected $enderecolocal;
    protected $enderecocobranca;
    protected $contatos;
    protected $esperapagamentoseguradora;

    public function getCodigo(){
        return $this->codigo;
    }
    public function getNomefantasia () {
        return $this->nomefantasia;
    }
    public function getRazaosocial () {
        return $this->razaosocial;
    }
    public function getCnpjcpf () {
        return $this->cnpjcpf;
    }
    public function getInscricaomunicipal () {
        return $this->inscricaomunicipal;
    }
    public function getStatus () {
        return $this->status;
    }
    public function getAnotacoes () {
        return $this->anotacoes;
    }
    public function getTiposatividades () {
        return $this->tiposatividades;
    }
    public function getEnderecolocal () {
        return $this->enderecolocal;
    }
    public function getEnderecocobranca () {
        return $this->enderecocobranca;
    }
    public function getContatos () {
        return $this->contatos;
    }
    public function getEsperapagamentoseguradora(){
        return $this->esperapagamentoseguradora;
    }

    public function setCodigo($codigo){
        $this->codigo = $codigo;
    }
    public function setNomefantasia ($nomefantasia){
        $this->nomefantasia = $nomefantasia;
    }
    public function setRazaosocial ($razaosocial){
        $this->razaosocial = $razaosocial;
    }
    public function setCnpjcpf ($cnpjcpf){
        $this->cnpjcpf = $cnpjcpf;
    }
    public function setInscricaomunicipal ($inscricaomunicipal){
        $this->inscricaomunicipal = $inscricaomunicipal;
    }
    public function setStatus ($status){
        $this->status = $status;
    }
    public function setAnotacoes ($anotacoes){
        $this->anotacoes = $anotacoes;
    }
    public function addTiposatividades ($tipoatividade){
        $this->tiposatividades[] = $tipoatividade;
    }
    public function setEnderecolocal ($enderecolocal){
        $this->enderecolocal = $enderecolocal;
    }
    public function setEnderecocobranca ($enderecocobranca){
        $this->enderecocobranca = $enderecocobranca;
    }
    public function setContatos ($contatos){
        $this->contatos = $contatos;
    }
    public function setEsperapagamentoseguradora ($esperapagamentoseguradora){
        $this->esperapagamentoseguradora = $esperapagamentoseguradora;
    }




    protected $dadosbancarios;
    
    public function getDadosbancarios () {
        return $this->dadosbancarios;
    }
    public function setDadosbancarios ($dadosbancarios){
        $this->dadosbancarios = $dadosbancarios;
    }

    public function fillDTO($arrDados){
        $this->codigo = isset($arrDados['codigo']) ? $arrDados['codigo'] : '';
        $this->nomefantasia = isset($arrDados['nomefantasia']) ? $arrDados['nomefantasia'] : '';
        $this->razaosocial = isset($arrDados['razaosocial']) ? $arrDados['razaosocial'] : '';
        $this->cnpjcpf = isset($arrDados['cnpjcpf']) ? $arrDados['cnpjcpf'] : '';
        $this->inscricaomunicipal = isset($arrDados['inscricaomunicipal']) ? $arrDados['inscricaomunicipal'] : '';
        $this->status = isset($arrDados['status']) ? $arrDados['status'] : '';
        $this->anotacoes = isset($arrDados['anotacoes']) ? $arrDados['anotacoes'] : '';
        $this->esperapagamentoseguradora = isset($arrDados['esperapagamentoseguradora']) ? $arrDados['esperapagamentoseguradora'] : '';

        $this->enderecolocal = new EnderecoDTO();
        $this->enderecolocal->fillDTO($arrDados['enderecolocal']);
        
        $this->enderecocobranca = new EnderecoDTO();
        $this->enderecocobranca->fillDTO($arrDados['enderecocobranca']);
        
        foreach ($arrDados['tiposatividades'] as $key => $tipoatividade) {
            $tipoatividadeDTO = new TipoAtividadeDTO();
            $tipoatividadeDTO->fillDTO($tipoatividade);
            $this->tiposatividades[] = $tipoatividadeDTO;
        }

        foreach ($arrDados['contatos'] as $key => $contato) {
            $contatoDTO = new ContatosDTO();
            $contatoDTO->fillDTO($contato);
            $this->contatos[] = $contatoDTO;
        }
        
        foreach ($arrDados['dadosbancarios'] as $key => $dadobancario) {
            $dadoBancarioDTO = new DadoBancarioDTO;
            $dadoBancarioDTO->fillDTO($dadobancario);
            $this->dadosbancarios[] = $dadoBancarioDTO;
        }

    }
}
