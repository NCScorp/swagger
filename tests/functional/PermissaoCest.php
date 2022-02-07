<?php

use Codeception\Util\HttpCode;
use AppBundle\Resources\Permissoes;

/**
 * Testa permissões dos usuário para cada área do sistema
 * [x] informes - lista
 * [x] recibos - lista
 * @todo pegar permissões do sistema diretório
 */
class PermissaoCest{

    /**
     * Lista de ações permitidas:
     * 
     */

    private $url_base = '/gednasajon';
    private $url_informes = 'informesrendimentos';
    private $url_recibospagamentos = 'recibospagamentos';
    private $url_salariosobdemanda = 'solicitacoes/salarios';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $estabelecimento = '39836516-7240-4fe5-847b-d5ee0f57252d';

    /**
     * @return array
     */
    protected function UsuarioProvider()
    {
        return [
            [
                'usuario' => 'usuariopermissao1@nasajon.com.br',
                'permissoes' => [],
                'id' => 1
            ],
            [
                'usuario' => 'usuariopermissao2@nasajon.com.br',
                'permissoes' => [],
                'id' => 2
            ],
        ];
    }

    /**
     * Executado depois de cada método da classe
     */
    public function  _after(FunctionalTester $I, \Codeception\Example $usuario){
        $I->deleteTrabalhador($I, $usuario['usuario'], $this->tenant_numero);
    }

    /**
     * Executado antes de cada método da classe
     */
    public function  _before(FunctionalTester $I){
        foreach ($this->UsuarioProvider() as $usuario ){
            $I->deleteTrabalhador($I, $usuario['usuario'], $this->tenant_numero);
        }
    }

}