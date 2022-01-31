<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Funções
 */
class ContaPagarCest{
    private $url_base = '/api/gednasajon/{atc}/atcscontasapagar/';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = 'b4c12f6c-e637-48e3-a858-cf5a04e12603';
    private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 

    /**
     *
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [
            EnumAcao::ATCSCONTASPAGAR_GERENCIAR
        ]);
    }

    /**
     *
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
        $I->deleteAllFromDatabase('crm.atcscontasapagar');
    }

    /**
     * Alterar uma conta a pagar
     * @param FunctionalTester $I
     */
    public function alterar(FunctionalTester $I){
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado1 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2, // Aprovado
            'servicotipo' => 1, // Serviço Externo
            'fornecedorterceirizado' => $fornecedorTerceirizado1['fornecedor']
        ]);
        $contapagar= $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado1,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento1['orcamento']
        ]);


        $contapagar['quantidade'] = 2;
        $contapagar['valorpagar'] = 30;
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', str_replace("{atc}", $atc['negocio'], $this->url_base) . $contapagar['atccontaapagar'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $contapagar, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
            $I->canSeeInDatabase('crm.atcscontasapagar', [
                'atccontaapagar' => $contapagar['atccontaapagar'], 
                'quantidade' => $contapagar['quantidade'],
                'valorpagar' => $contapagar['valorpagar'],
                'tenant' => $I->tenant_numero,
            ]);
            $I->canSeeInDatabase('crm.historicoatcs', [
                'negocio' => $atc['negocio'],
                'secao' => 'contasapagar',
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
    }

    /**
     * Testa busca de todas as configurações de lista da vez. Será utilizada para todos os testes de filtros.
     */
    private function _getAll(FunctionalTester $I, $atc, $filtros = [], $dadosEsperados = []){
        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('GET', str_replace("{atc}", $atc, $this->url_base) . '?' . http_build_query($filtros), [], [], [], null);
    
        /* Validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(count($dadosEsperados), count($retorno));
        
        for ($i=0; $i < count($dadosEsperados); $i++) {
            $dadoEsperado = $dadosEsperados[$i];
    
            //Uso um array_values somente para reorganizar os ID's, já que o array_filter nem sempre retorna o id 0 mesmo contendo um valor.
            $arrRetorno = array_values( array_filter($retorno, function($_dadoRetorno) use ($dadoEsperado) {
                return ($dadoEsperado['atccontaapagar'] == $_dadoRetorno['atccontaapagar']);
            }) );
    
            $I->assertEquals(1, count($arrRetorno));
            $itemRetorno = $arrRetorno[0];
 
            $I->assertEquals($dadoEsperado['atc'], $itemRetorno['atc']);
            $I->assertEquals($dadoEsperado['prestador']['fornecedor'], $itemRetorno['prestador']['fornecedor']);
            $I->assertEquals($dadoEsperado['servico'], $itemRetorno['servico']);
            $I->assertEquals($dadoEsperado['orcamento'], $itemRetorno['orcamento']);
            $I->assertEquals($dadoEsperado['descricao'], $itemRetorno['descricao']);
            $I->assertEquals($dadoEsperado['quantidade'], $itemRetorno['quantidade']);
            $I->assertEquals($dadoEsperado['negociodocumento'], $itemRetorno['negociodocumento']);
            $I->assertEquals($dadoEsperado['numerodocumento'], $itemRetorno['numerodocumento']);
        }
    
        return $retorno;
    }

    /**
     * Testa busca de todas as configuracoes de lista da vez
     */
    public function listar(FunctionalTester $I){
        /* Preparação do cenário */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado1 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado2 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2, // Aprovado
            'servicotipo' => 1, // Serviço Externo
            'fornecedorterceirizado' => $fornecedorTerceirizado1['fornecedor']
        ]);
        $dados = [];
        $dados[] = $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado1,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento1['orcamento']
        ]);
        $dados[] = $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado2,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento1['orcamento']
        ]);
        
        $filtros = [
            'grupoempresarial' => $I->grupoempresarial
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_getAll($I, $atc['negocio'], $filtros, $dados);
    }
}