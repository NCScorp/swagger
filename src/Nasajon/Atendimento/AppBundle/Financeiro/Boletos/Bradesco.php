<?php

namespace Nasajon\Atendimento\AppBundle\Financeiro\Boletos;

use Nasajon\Atendimento\AppBundle\Financeiro\Boletos;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Titulos;
use OpenBoleto\Agente;
use Symfony\Component\HttpFoundation\Response;
use \DateTime;

class Bradesco implements Boletos {

    private function mask($val, $mask) {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            }
            else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    private function geraCedente(Titulos $titulo) {
        $estabelecimento = $titulo->getEstabelecimento();
        $enderecoCedente = $estabelecimento->getTipologradouro() . " " . $estabelecimento->getLogradouro() . " " . $estabelecimento->getNumerologradouro() . " " . $estabelecimento->getComplementologradouro(). " - " . $estabelecimento->getBairrologradouro();

        return new Agente($estabelecimento->getNomefantasia(), $this->mask($estabelecimento->getRaizcnpj() . $estabelecimento->getOrdemcnpj(), '##.###.###/####-##'), $enderecoCedente, $this->mask($estabelecimento->getCeplogradouro(), '#####-###'), $estabelecimento->getCidadelogradouro(), 'RJ');
    }

    private function geraSacado(Titulos $titulo) {

        $endereco = $titulo->getEnderecocobranca();
        $enderecoCedente = $endereco->getTipologradouro() . " " . $endereco->getLogradouro() . " " . $endereco->getNumero() . " " . $endereco->getComplemento()." - ". $endereco->getBairro();

        return new Agente($titulo->getCliente()->getNome(), $titulo->getCliente()->getCnpj(), $enderecoCedente, $this->mask($endereco->getCep(), '#####-###'), $endereco->getMunicipio(), $endereco->getUf());
    }

    private function macroInstrucao(Titulos $titulo, $linha) {
        $macros = [
            "<00>", "<01>", "<02>", "<03>", "<04>", "<05>", "<06>", "<07>", "<08>", "<09>",
            "<10>", "<11>", "<12>", "<13>", "<14>", "<15>", "<16>", "<17>", "<18>", "<19>",
            "<20>", "<21>", "<22>", "<23>", "<24>", "<25>", "<26>", "<27>", "<28>", "<29>",
            "<30>", "<31>",
        ];
        $items = [
            $titulo->getMulta(), //00
            $titulo->getJuros(), //01
            $titulo->getNumero(), //02
            $titulo->getEmissao(), //03
            $titulo->getVencimento(), //04
            $titulo->getValor(), //05
            $titulo->getAliquotair(), //06
            $titulo->getAliquotapis(), //07
            $titulo->getAliquotacofins(), //08
            $titulo->getAliquotacsll(), //09
            $titulo->getAliquotaiss(), //10
            ($titulo->getAliquotapis() + $titulo->getAliquotacofins() + $titulo->getAliquotacsll()), //11 PCC
            $titulo->getIrretido(), //12
            $titulo->getPisretido(), //13
            $titulo->getCofinsretido(), //14
            $titulo->getCsllretido(), //15 
            $titulo->getIssretido(), //16
            ($titulo->getPisretido() + $titulo->getCofinsretido() + $titulo->getCsllretido()), //17 PCC
            $titulo->getCliente()->getCodigo(), //18
            $titulo->getCliente()->getNome(), //19
            "", //20 TODO:
            $titulo->getObservacao(), //21
            $titulo->getPercentualmulta(), //22
            $titulo->getPercentualjurosdiario(), //23
            "", //24 Notas TODO:
            $titulo->getDesconto(), //25
            $titulo->getDatalimitedesconto(), //26
            "", //27 TODO:
            "", //28 TODO:
            "", //29 TODO:            
            $titulo->getInssretido(), //30
            ($titulo->getIssretido() + $titulo->getInssretido()) //31
        ];

        return str_replace($macros, $items, $linha);
    }

    private function getInstrucoes(Titulos $titulo) {
        return [
            $this->macroInstrucao($titulo, $titulo->getConfiguracoesbancarias()['linha3']),
            $this->macroInstrucao($titulo, $titulo->getConfiguracoesbancarias()['linha4']),
            $this->macroInstrucao($titulo, $titulo->getConfiguracoesbancarias()['linha5']),
            $this->macroInstrucao($titulo, $titulo->getConfiguracoesbancarias()['linha6']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function gerar(Titulos $titulo) {

        $boleto = new \OpenBoleto\Banco\Bradesco();

        $boleto
                ->setLogoPath('//s3-us-west-2.amazonaws.com/static.nasajon/img/logo.png')
                ->setDataVencimento(new DateTime($titulo->getVencimento()))
                ->setDataDocumento(new DateTime($titulo->getEmissao()))
                ->setNumeroDocumento($titulo->getNossonumero())
                ->setSequencial($titulo->getNossonumero())
                ->setValor($titulo->getValorbruto())
                ->setSacado($this->geraSacado($titulo))
                ->setCedente($this->geraCedente($titulo))
//                ->setAceite($titulo->getAceite())
                ->setCarteira($titulo->getConfiguracoesbancarias()['carteira'])
                ->setAgencia($titulo->getConta()->getAgencianumero())
                ->setConta($titulo->getConta()->getNumero())
                ->setContaDv($titulo->getConta()->getDigito())
                ->setAgenciaDv($titulo->getConta()->getAgenciadigito())
                ->setInstrucoes($this->getInstrucoes($titulo))
                ->setDescricaoDemonstrativo($this->getInstrucoes($titulo))
                ->setLocalPagamento("Pagável preferencialmente na Rede Bradesco ou Bradesco Expresso");

        return new Response($boleto->getOutput(), Response::HTTP_OK, array('content-type' => 'text/html'));
    }

}
