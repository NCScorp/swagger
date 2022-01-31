<?php

namespace Nasajon\AppBundle\DTO\Crm;

class ServicoOrcadoDTO
{
    protected $descricao;
    protected $valor;
    protected $quantidade;
    protected $descontoParcial;
    protected $descontoGlobal;
    protected $valorAutorizado;
    protected $fornecedorNomefantasia;
    protected $fornecedorRazaosocial;

    public function fillDTO($arrDados)
    {
        $this->descricao = isset($arrDados['descricao']) ? $arrDados['descricao'] : '';
        $this->valor = isset($arrDados['valor']) ? $arrDados['valor'] : 0.00;
        $this->quantidade = isset($arrDados['quantidade']) ? $arrDados['quantidade'] : 0;
        $this->descontoParcial = isset($arrDados['descontoparcial']) ? $arrDados['descontoparcial'] : 0.00;
        $this->descontoGlobal = isset($arrDados['descontoglobal']) ? $arrDados['descontoglobal'] : 0.00;
        $this->valorAutorizado = isset($arrDados['valorreceber']) ? $arrDados['valorreceber'] : 0.00;
        $this->fornecedorNomefantasia = isset($arrDados['fornecedor_nomefantasia']) ? $arrDados['fornecedor_nomefantasia'] : 0.00;
        $this->fornecedorRazaosocial = isset($arrDados['fornecedor_razaosocial']) ? $arrDados['fornecedor_razaosocial'] : 0.00;
    }

    public function getDescricao () {
        return $this->descricao;
    }
    public function getValor () {
        return $this->valor;
    }
    public function getQuantidade() {
        return $this->quantidade;
    }
    public function getDescontoParcial() {
        return $this->descontoParcial;
    }
    public function getDescontoGlobal() {
        return $this->descontoGlobal;
    }
    public function getFornecedorNomefantasia() {
        return $this->fornecedorNomefantasia;
    }
    public function getFornecedorRazaosocial() {
        return $this->fornecedorRazaosocial;
    }

    public function setDescricao ($descricao) {
        $this->descricao = $descricao;
    }
    public function setValor ($valor) {
        $this->valor = $valor;
    }
    public function setQuantidade ($quantidade) {
        $this->quantidade = $quantidade;
    }
    public function setDescontoParcial ($descontoParcial) {
        $this->descontoParcial = $descontoParcial;
    }
    public function setDescontoGlobal ($descontoGlobal) {
        $this->descontoGlobal = $descontoGlobal;
    }
    public function setFornecedorNomefantasia ($fornecedorNomefantasia) {
        $this->fornecedorNomefantasia = $fornecedorNomefantasia;
    }
    public function setFornecedorRazaosocial ($fornecedorRazaosocial) {
        $this->fornecedorRazaosocial = $fornecedorRazaosocial;
    }


    /**
     * Get the value of valorAutorizado
     */ 
    public function getValorAutorizado()
    {
        return $this->valorAutorizado;
    }

    /**
     * Set the value of valorAutorizado
     *
     * @return  self
     */ 
    public function setValorAutorizado($valorAutorizado)
    {
        $this->valorAutorizado = $valorAutorizado;

        return $this;
    }
}
