<?php

namespace Nasajon\AppBundle\DTO\Ns;

use Nasajon\AppBundle\DTO\Ns\TelefonesDTO;

class ContatosDTO
{
    private $nome;
    private $primeironome;
    private $sobrenome;
    private $principal;
    private $cargo;
    private $setor;
    private $email;
    private $observacao;

    private $telefones;

    public function fillDTO($arrDados)
    {
        $this->nome = isset($arrDados['nome']) ? $arrDados['nome'] : '';
        $this->primeironome = isset($arrDados['primeironome']) ? $arrDados['primeironome'] : '';
        $this->sobrenome = isset($arrDados['sobrenome']) ? $arrDados['sobrenome'] : '';
        $this->principal = isset($arrDados['principal']) ? $arrDados['principal'] : '';
        $this->cargo = isset($arrDados['cargo']) ? $arrDados['cargo'] : '';
        $this->setor = isset($arrDados['setor']) ? $arrDados['setor'] : '';
        $this->email = isset($arrDados['email']) ? $arrDados['email'] : '';
        $this->observacao = isset($arrDados['observacao']) ? $arrDados['observacao'] : '';
        
        if(isset($arrDados['telefones'])){
            foreach ($arrDados['telefones'] as $key => $telefone) {
                $telefoneDTO = new TelefonesDTO();
                $telefoneDTO->fillDTO($telefone);
                $this->telefones[] = $telefoneDTO;
            }
        }
    }

    public function getNome () {
        return $this->nome;
    }
    public function getPrimeironome () {
        return $this->primeironome;
    }
    public function getSobrenome () {
        return $this->sobrenome;
    }
    public function getPrincipal () {
        return $this->principal;
    }
    public function getCargo () {
        return $this->cargo;
    }
    public function getSetor () {
        return $this->setor;
    }
    public function getEmail () {
        return $this->email;
    }
    public function getObservacao () {
        return $this->observacao;
    }
    public function getTelefones() {
        return $this->telefones;
    }

    public function setNome ($nome) {
        $this->nome = $nome;
    }
    public function setPrimeironome ($primeironome) {
        $this->primeironome = $primeironome;
    }
    public function setSobrenome ($sobrenome) {
        $this->sobrenome = $sobrenome;
    }
    public function setPrincipal ($principal) {
        $this->principal = $principal;
    }
    public function setCargo ($cargo) {
        $this->cargo = $cargo;
    }
    public function setSetor ($setor) {
        $this->setor = $setor;
    }
    public function setEmail ($email) {
        $this->email = $email;
    }
    public function setObservacao ($observacao) {
        $this->observacao = $observacao;
    }
    public function setTelefones ($telefones) {
        $this->telefones = $telefones;
    }


}
