<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Configuração da lista da vez
 */
class CrmListaDaVezConfiguracoesCest {
    /**
     * Url base de requisições do teste.
     */
    private $url = '/lista-da-vez-configuracoes/';
    
    /**
     * Guarda as dependências utilizadas no cenário para montar a Entidade
     */
    private $cenario = [];

    /**
     * Testa busca de todas as configurações de lista da vez. Será utilizada para todos os testes de filtros.
     */
    private function _getAll(FunctionalTester $I, $filtros = [], $dadosEsperados = []){
        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('GET', 'api/' . $I->tenant . $this->url . '?' . http_build_query($filtros), [], [], [], null);
    
        /* Validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(count($dadosEsperados), count($retorno));
        
        for ($i=0; $i < count($dadosEsperados); $i++) {
            $dadoEsperado = $dadosEsperados[$i];
    
            //Uso um array_values somente para reorganizar os ID's, já que o array_filter nem sempre retorna o id 0 mesmo contendo um valor.
            $arrRetorno = array_values( array_filter($retorno, function($_dadoRetorno) use ($dadoEsperado) {
                return ($dadoEsperado['listadavezconfiguracao'] == $_dadoRetorno['listadavezconfiguracao']);
            }) );
    
            $I->assertEquals(1, count($arrRetorno));
            $itemRetorno = $arrRetorno[0];
 
            $I->assertEquals($dadoEsperado['listadavezregra']['listadavezregra'], $itemRetorno['listadavezregra']['listadavezregra']);
            $I->assertEquals($dadoEsperado['idlistadavezregravalor']['listadavezregravalor'], $itemRetorno['idlistadavezregravalor']['listadavezregravalor']);
            $I->assertEquals($dadoEsperado['idestado']['uf'], $itemRetorno['idestado']['uf']);
            $I->assertEquals($dadoEsperado['idnegociooperacao']['proposta_operacao'], $itemRetorno['idnegociooperacao']['proposta_operacao']);
            $I->assertEquals($dadoEsperado['idsegmentoatuacao']['segmentoatuacao'], $itemRetorno['idsegmentoatuacao']['segmentoatuacao']);
            $I->assertEquals($dadoEsperado['idpai'], $itemRetorno['idpai']);
            $I->assertEquals($dadoEsperado['listadavezvendedor']['listadavezvendedor'], $itemRetorno['listadavezvendedor']['listadavezvendedor']);
            
            $I->assertEquals($dadoEsperado['tiporegistro'], $itemRetorno['tiporegistro']);
            $I->assertEquals($dadoEsperado['ordem'], $itemRetorno['ordem']);
            $I->assertEquals($dadoEsperado['vendedorfixo'], $itemRetorno['vendedorfixo']);
        }
    
        return $retorno;
    }

    /**
     * Monta o cenário para fazer testes
     */
    private function montarCenario(FunctionalTester $I, $arrReutilizarCenario = []){
        if (!array_key_exists('regra.cliente', $arrReutilizarCenario)){    
            $this->cenario['regra.cliente'] = $I->haveInDatabaseListadaVezRegra($I, [
                'totalvalores' => 2
            ]);
        }
        if (!array_key_exists('regra.cliente.valor.sim', $arrReutilizarCenario)){    
            $this->cenario['regra.cliente.valor.sim'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.cliente'],
                'valor' => '1'
            ]);
        }
        if (!array_key_exists('regra.cliente.valor.nao', $arrReutilizarCenario)){    
            $this->cenario['regra.cliente.valor.nao'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.cliente'],
                'valor' => '2'
            ]);
        }
    }

    /**
    * Retorna a estrutura simples da configuração de lista da vez, sem criar o mock no banco
    * FunctionalTester $I
    * Array $arrReutilizarCenario: Não recriar cenários que estejam neste array.
    */
    private function getEstruturaListaDaVezConfiguracao(FunctionalTester $I, $arrReutilizarCenario = [], $dadosExtras = []){
        /* Prepara cenário */
        $this->montarCenario($I, $arrReutilizarCenario);

        /* Monto objeto da requisição */
        $dados = [
            'idestado' => (isset($dadosExtras['idestado']) ? $dadosExtras['idestado'] : null),
            'idnegociooperacao' => (isset($dadosExtras['idnegociooperacao']) ? $dadosExtras['idnegociooperacao'] : null),
            'idsegmentoatuacao' => (isset($dadosExtras['idsegmentoatuacao']) ? $dadosExtras['idsegmentoatuacao'] : null),
            'listadavezvendedor' => (isset($dadosExtras['listadavezvendedor']) ? $dadosExtras['listadavezvendedor'] : null),
            'listadavezregra' => (isset($dadosExtras['listadavezregra']) ? $dadosExtras['listadavezregra'] : $this->cenario['regra.cliente']),
            'idlistadavezregravalor' => (isset($dadosExtras['idlistadavezregravalor']) ? $dadosExtras['idlistadavezregravalor'] : null),
            'ordem' => (isset($dadosExtras['ordem']) ? $dadosExtras['ordem'] : 0),
            'idpai' => (isset($dadosExtras['idpai']) ? $dadosExtras['idpai'] : null),
            'tiporegistro' => (isset($dadosExtras['tiporegistro']) ? $dadosExtras['tiporegistro'] : '0'),
            'vendedorfixo' => (isset($dadosExtras['vendedorfixo']) ? $dadosExtras['vendedorfixo'] : false),
            'listadavezconfiguracaonovo' => (isset($dadosExtras['listadavezconfiguracaonovo']) ? $dadosExtras['listadavezconfiguracaonovo'] : '1'),
            'idpainovo' => (isset($dadosExtras['idpainovo']) ? $dadosExtras['idpainovo'] : null),
        ];
        
        return $dados;
    }
    
    /**
     * Retorna a estrutura simples da configuração de lista da vez para alterar
     * FunctionalTester $I
     * Array $arrReutilizarCenario: Não recriar cenários que estejam neste array.
     * Array $dadosOS: Dados a serem alterados no objeto da os.
     */
    private function getEstruturaListaDaVezConfiguracaoAlterar(FunctionalTester $I, $arrReutilizarCenario = [], $dadosExtras = []){
        //Busco a estrutura simples da OS
        $dados = $this->getEstruturaListaDaVezConfiguracao($I, $arrReutilizarCenario, $dadosExtras);

        $listaDaVezConfiguracao = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, $dados);

        return $listaDaVezConfiguracao;
    }
    
    /**
     * Valida as configurações em forma de árvore.
     * Para isso, busca o guid das configurações e depois preenche o guid de referência da configuração pai.
     * Obs: Nos casos que todos os dados estão sendo criados, não criar registros identicos, pois não será possível
     *  buscar o guid desse registro corretamente.
     */
    private function validaArvore(FunctionalTester $I, $listaDaVezConfiguracaoLote, $arrConfiguracoesExcluidas = []){
        // Preencho guid das configurações
        foreach ($listaDaVezConfiguracaoLote['listadavezconfiguracoes'] as &$listadavezconfiguracao) {
            $guid = isset($listadavezconfiguracao['listadavezconfiguracao']) ? $listadavezconfiguracao['listadavezconfiguracao'] : null;

            if ($guid == null) {
                $guid = $I->grabFromDatabase('crm.listadavezconfiguracoes', 'listadavezconfiguracao', [
                    'id_listadavezregra' => $listadavezconfiguracao['listadavezregra']['listadavezregra'],
                    'id_listadavezregravalor' => $listadavezconfiguracao['idlistadavezregravalor'] ? $listadavezconfiguracao['idlistadavezregravalor']['listadavezregravalor'] : null,
                    'id_estado' => $listadavezconfiguracao['idestado'] ? $listadavezconfiguracao['idestado']['uf'] : null,
                    'id_negociooperacao' => $listadavezconfiguracao['idnegociooperacao'] ? $listadavezconfiguracao['idnegociooperacao']['proposta_operacao'] : null,
                    'id_segmentoatuacao' => $listadavezconfiguracao['idsegmentoatuacao'] ? $listadavezconfiguracao['idsegmentoatuacao']['segmentoatuacao'] : null,
                    'tiporegistro' => $listadavezconfiguracao['tiporegistro'],
                    'ordem' => $listadavezconfiguracao['ordem'],
                    'vendedorfixo' => $listadavezconfiguracao['vendedorfixo'],
                    'listadavezvendedor' => $listadavezconfiguracao['listadavezvendedor'] ? $listadavezconfiguracao['listadavezvendedor']['listadavezvendedor'] : null,
                    'tenant' => $I->tenant_numero,
                    'id_grupoempresarial' => $I->id_grupoempresarial
                ]);

                $listadavezconfiguracao['listadavezconfiguracao'] = $guid;
            }
        }

        // Preencho guid referente a configuração pai
        $arrColunasId = array_column($listaDaVezConfiguracaoLote['listadavezconfiguracoes'], 'listadavezconfiguracao');
        $arrColunasIdNovo = array_column($listaDaVezConfiguracaoLote['listadavezconfiguracoes'], 'listadavezconfiguracaonovo');
        
        foreach ($listaDaVezConfiguracaoLote['listadavezconfiguracoes'] as &$listadavezconfiguracao) {
            if ($listadavezconfiguracao['idpai'] == null && $listadavezconfiguracao['idpainovo'] != null) {
                $keypai = array_search($listadavezconfiguracao['idpainovo'], $arrColunasIdNovo);
                $listadavezconfiguracao['idpai'] = $arrColunasId[$keypai];
            }
        }

        // Valido dados, já considerando chaves da configuração e da configuração pai
        foreach ($listaDaVezConfiguracaoLote['listadavezconfiguracoes'] as $listadavezconfiguracao) {
            $I->canSeeInDatabase('crm.listadavezconfiguracoes', [
                'listadavezconfiguracao' => $listadavezconfiguracao['listadavezconfiguracao'],
                'id_pai' => $listadavezconfiguracao['idpai'],
                'id_listadavezregra' => $listadavezconfiguracao['listadavezregra']['listadavezregra'],
                'id_listadavezregravalor' => $listadavezconfiguracao['idlistadavezregravalor'] ? $listadavezconfiguracao['idlistadavezregravalor']['listadavezregravalor'] : null,
                'id_estado' => $listadavezconfiguracao['idestado'] ? $listadavezconfiguracao['idestado']['uf'] : null,
                'id_negociooperacao' => $listadavezconfiguracao['idnegociooperacao'] ? $listadavezconfiguracao['idnegociooperacao']['proposta_operacao'] : null,
                'id_segmentoatuacao' => $listadavezconfiguracao['idsegmentoatuacao'] ? $listadavezconfiguracao['idsegmentoatuacao']['segmentoatuacao'] : null,
                'tiporegistro' => $listadavezconfiguracao['tiporegistro'],
                'ordem' => $listadavezconfiguracao['ordem'],
                'vendedorfixo' => $listadavezconfiguracao['vendedorfixo'],
                'listadavezvendedor' => $listadavezconfiguracao['listadavezvendedor'] ? $listadavezconfiguracao['listadavezvendedor']['listadavezvendedor'] : null,
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }

        // Valido a exclusão dos itens
        foreach ($arrConfiguracoesExcluidas as $listadavezconfiguracao) {
            $I->cantSeeInDatabase('crm.listadavezconfiguracoes', [
                'listadavezconfiguracao' => $listadavezconfiguracao['listadavezconfiguracao'],
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Função que roda antes de qualquer teste
     */
    public function _before(FunctionalTester $I) {
        // Faz o mock do usuário e suas permissões
        $I->amSamlLoggedInAs('colaborador@empresa.com.br', [
            EnumAcao::LISTADAVEZCONFIGURACOES
        ]);
    }

    /**
     * Cria a configuração com regra inicial fixa com um caso alternativo
     * @param FunctionalTester $I
     */
    public function criaRegraFixaComCasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Cria a configuração com regra inicial fixa, uma opção e um caso alternativo
     * @param FunctionalTester $I
     */
    public function criaRegraFixaComOpcaoECasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim'],
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Cria a configuração com regra inicial fixa, total de opçãoe e sem caso alternativo
     * @param FunctionalTester $I
     */
    public function criaRegraFixaTotalOpcoesSemCasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim'],
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.nao'],
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Cria a configuração com regra inicial estado, uma opção e um caso alternativo
     * @param FunctionalTester $I
     */
    public function criaRegraEstadoComOpcaoECasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);
        $estado = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A1'
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraEstado,
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'listadavezregra' => $regraEstado,
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Cria a configuração com regra inicial negócio operação, uma opção e um caso alternativo
     * @param FunctionalTester $I
     */
    public function criaRegraNegocioOperacaoComOpcaoECasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $regraNegocioOperacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '2' // Negócio operação
        ]);
        $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraNegocioOperacao,
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraNegocioOperacao,
            'idnegociooperacao' => $negocioOperacao,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'listadavezregra' => $regraNegocioOperacao,
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Cria a configuração com regra inicial segmento de atuação, uma opção e um caso alternativo
     * @param FunctionalTester $I
     */
    public function criaRegraSegmentoAtuacaoComOpcaoECasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $regraSegmentoAtuacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '3' // Segmento de atuação
        ]);
        $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraSegmentoAtuacao,
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'idsegmentoatuacao' => $segmentoAtuacao,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'listadavezregra' => $regraSegmentoAtuacao,
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Cria a configuração com regra inicial fixa, duas opções, e uma das opções tem regra filha de estado.
     * @param FunctionalTester $I
     */
    public function criaRegraFixaComDuasOpcoesComRegraFilhaEstado(FunctionalTester $I){
        /* Preparação do cenário */
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);
        $estado1 = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A1'
        ]);
        $estado2 = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A2'
        ]);
        $listadavezvendedor1 = $I->haveInDatabaseListadavezvendedor($I);
        $listadavezvendedor2 = $I->haveInDatabaseListadavezvendedor($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim'],
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.nao'],
        ]);
        // 1.2.1 - Regra
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1',
            'idpainovo' => '1.2',
            'listadavezregra' => $regraEstado
        ]);
        // 1.2.1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1.1',
            'idpainovo' => '1.2.1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado1,
            'listadavezvendedor' => $listadavezvendedor1
        ]);
        // 1.2.1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1.2',
            'idpainovo' => '1.2.1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado2,
            'listadavezvendedor' => $listadavezvendedor2
        ]);
        // 1.2.1.3 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1.3',
            'idpainovo' => '1.2.1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraEstado,
            'listadavezvendedor' => $listadavezvendedor1
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Altera a configuração de lista da vez
     *  - Adiciona arvore inicial no banco com 7 configurações
     *  - Altera 3 configurações
     *  - Exclui 4 configurações
     *  - Adiciona 4 configurações
     * @param FunctionalTester $I
     */
    public function alteraTresConfiguracoesExcluiRegraEstadoAdicionaRegraSegmentoAtuacao(FunctionalTester $I){
        /* Preparação do cenário */
        // Crio regra no banco
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);
        // Crio estados no banco
        $estado1 = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A1'
        ]);
        $estado2 = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A2'
        ]);
        // Crio regra no banco
        $regraSegmentoAtuacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '3' // Segmento de atuação
        ]);
        // Crio segmentos de atuação no banco
        $segmentoAtuacao1 = $I->haveInDatabaseSegmentoAtuacao($I);
        $segmentoAtuacao2 = $I->haveInDatabaseSegmentoAtuacao($I, [
            'codigo' => 'SEG02'
        ]);

        // Crio listas de vendedores no banco
        $listadavezvendedor1 = $I->haveInDatabaseListadavezvendedor($I);
        $listadavezvendedor2 = $I->haveInDatabaseListadavezvendedor($I);

        // Lista de configurações que serão excluídas
        $arrListaConfiguracaoExcluir = [];

        // Objeto para salvar as configurações em Lote
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];

        // Registros que serão alterados
        // 1 - Pai
        $conf_1 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I);
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1;
        // 1.1 - Opção
        $conf_1_1 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I, $this->cenario, [
            'idpai' => $conf_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim'],
        ]);
        $conf_1_1['vendedorfixo'] = false;
        $conf_1_1['listadavezvendedor'] = $listadavezvendedor1;
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1_1;
        // 1.2 - Opção
        $conf_1_2 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I, $this->cenario, [
            'idpai' => $conf_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.nao'],
        ]);
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1_2;

        // Registros que serão excluídos
        // 1.2.1 - Regra
        $conf_1_2_1 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I, $this->cenario, [
            'idpai' => $conf_1_2['listadavezconfiguracao'],
            'listadavezregra' => $regraEstado
        ]);
        $arrListaConfiguracaoExcluir[] = $conf_1_2_1;
        // 1.2.1.1 - Opção
        $conf_1_2_1_1 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I, $this->cenario, [
            'idpai' => $conf_1_2_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado1,
            'listadavezvendedor' => $listadavezvendedor1
        ]);
        $arrListaConfiguracaoExcluir[] = $conf_1_2_1_1;
        // 1.2.1.2 - Opção
        $conf_1_2_1_2 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I, $this->cenario, [
            'idpai' => $conf_1_2_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado2,
            'listadavezvendedor' => $listadavezvendedor2
        ]);
        $arrListaConfiguracaoExcluir[] = $conf_1_2_1_2;
        // 1.2.1.3 - Caso alternativo
        $conf_1_2_1_3 = $this->getEstruturaListaDaVezConfiguracaoAlterar($I, $this->cenario, [
            'idpai' => $conf_1_2_1['listadavezconfiguracao'],
            'tiporegistro' => 2,
            'listadavezregra' => $regraEstado,
            'listadavezvendedor' => $listadavezvendedor1
        ]);
        $arrListaConfiguracaoExcluir[] = $conf_1_2_1_3;

        // Registros que serão adicionados
        // 1.2.1 - Regra
        $conf_1_2_1 = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1',
            'idpai' => $conf_1_2['listadavezconfiguracao'],
            'listadavezregra' => $regraSegmentoAtuacao
        ]);
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1_2_1;
        // 1.2.1.1 - Opção
        $conf_1_2_1_1 = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1.1',
            'idpainovo' => $conf_1_2_1['listadavezconfiguracaonovo'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'idsegmentoatuacao' => $segmentoAtuacao1,
            'listadavezvendedor' => $listadavezvendedor1
        ]);
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1_2_1_1;
        // 1.2.1.2 - Opção
        $conf_1_2_1_2 = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1.2',
            'idpainovo' => $conf_1_2_1['listadavezconfiguracaonovo'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'idsegmentoatuacao' => $segmentoAtuacao2,
            'listadavezvendedor' => $listadavezvendedor2
        ]);
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1_2_1_2;
        // 1.2.1.3 - Caso alternativo
        $conf_1_2_1_3 = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2.1.3',
            'idpainovo' => $conf_1_2_1['listadavezconfiguracaonovo'],
            'tiporegistro' => 2,
            'listadavezregra' => $regraSegmentoAtuacao,
            'listadavezvendedor' => $listadavezvendedor1
        ]);
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $conf_1_2_1_3;

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);

            $this->validaArvore($I, $listaDaVezConfiguracao, $arrListaConfiguracaoExcluir);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando a configuração inicial não for do tipo Regra
     * @param FunctionalTester $I
     */
    public function naoSalvarConfiguracaoInicialNaoERegra(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'tiporegistro' => 1
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);

        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra não fixa não tiver um caso alternativo
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraNaoFixaSemCasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);
        $estado = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A1'
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Regra Estado
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraEstado
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'listadavezregra' => $regraEstado,
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idestado' => $estado
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra fixa sem seu total de opção não tiver um caso alternativo
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraFixaSemTotalOpcoesSemCasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $regraFixa = $I->haveInDatabaseListadaVezRegra($I, [
            'totalvalores' => 2
        ]);
        $regraFixaValor1 = $I->haveInDatabaseListadaVezRegraValor($I, [
            'listadavezregra' => $regraFixa
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraFixa
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraFixa,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $regraFixaValor1,
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra se repetir nos filhos da árvore.
     * Exemplo: Se houver um pai com regra de Estado, nenhuma regra descendente pode ser de Estado.
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraRepetida(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);
        // 1.1.1 - Regra
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.1',
            'idpainovo' => '1.1'
        ]);
        // 1.1.1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.1.1',
            'idpainovo' => '1.1.1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra fixa tiver valores repetidos
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraFixaComValorRepetido(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim']
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim']
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra estado tiver valores repetidos
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraEstadoComValorRepetido(FunctionalTester $I){
        /* Preparação do cenário */
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);
        $estado = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A1'
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraEstado
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado,
            'vendedorfixo' => true
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado,
            'vendedorfixo' => true
        ]);
        // 1.3 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.3',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraEstado,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra negócio operação tiver valores repetidos
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraNegocioOperacaoComValorRepetido(FunctionalTester $I){
        /* Preparação do cenário */
        $regraNegocioOperacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '2' // Negócio Operação
        ]);
        $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraNegocioOperacao
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraNegocioOperacao,
            'idnegociooperacao' => $negocioOperacao,
            'vendedorfixo' => true
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraNegocioOperacao,
            'idnegociooperacao' => $negocioOperacao,
            'vendedorfixo' => true
        ]);
        // 1.3 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.3',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraNegocioOperacao,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra segmento de atuação tiver valores repetidos
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraSegmentoAtuacaoComValorRepetido(FunctionalTester $I){
        /* Preparação do cenário */
        $regraSegmentoAtuacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '3' // Segmento de atuação
        ]);
        $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraSegmentoAtuacao
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'idsegmentoatuacao' => $segmentoAtuacao,
            'vendedorfixo' => true
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'idsegmentoatuacao' => $segmentoAtuacao,
            'vendedorfixo' => true
        ]);
        // 1.3 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.3',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraSegmentoAtuacao,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma regra tiver vários casos alternativos
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraComVariosCasosAlternativos(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma opção/caso alternativo tiver regra diferente dos pais
     * @param FunctionalTester $I
     */
    public function naoSalvarOpcaoOuCasoAlternativoComRegraDiferenteDoPai(FunctionalTester $I){
        /* Preparação do cenário */
        $regraFixa = $I->haveInDatabaseListadaVezRegra($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraFixa,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma opção ou caso alternativo tiver um filho tipo opção
     * @param FunctionalTester $I
     */
    public function naoSalvarOpcaoOuCasoAlternativoComFilhoTipoOpcao(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2
        ]);
        // 1.1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.1',
            'idpainovo' => '1.1',
            'tiporegistro' => 1,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.nao'],
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma opção ou caso alternativo tiver um filho tipo caso alternativo
     * @param FunctionalTester $I
     */
    public function naoSalvarOpcaoOuCasoAlternativoComFilhoTipoCasoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2
        ]);
        // 1.1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.1',
            'idpainovo' => '1.1',
            'tiporegistro' => 2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma opção/caso alternativo não tiver filhos e não estiver com vendedorfixo marcado e
     *  sem lista da vez selecionada
     * @param FunctionalTester $I
     */
    public function naoSalvarOpcaoOuCasoAlternativoSemFilhoESemVendedorFixoESemListaDaVez(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando uma opção/caso alternativo tiver vários filhos tipo regra
     * @param FunctionalTester $I
     */
    public function naoSalvarOpcaoOuCasoAlternativoComVariasFilhosTipoRegra(FunctionalTester $I){
        /* Preparação do cenário */
        $regraFixa = $I->haveInDatabaseListadaVezRegra($I);
        $regraFixa2 = $I->haveInDatabaseListadaVezRegra($I);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1'
        ]);
        // 1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 2
        ]);
        // 1.1.1 - Regra
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.1',
            'idpainovo' => '1.1',
            'listadavezregra' => $regraFixa
        ]);
        // 1.1.1.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.1.1',
            'idpainovo' => '1.1.1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraFixa,
            'vendedorfixo' => true
        ]);
        // 1.1.2 - Regra
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.2',
            'idpainovo' => '1.1',
            'listadavezregra' => $regraFixa2
        ]);
        // 1.1.2.1 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1.2.1',
            'idpainovo' => '1.1.2',
            'tiporegistro' => 2,
            'listadavezregra' => $regraFixa2,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

     /**
     * Retornar BadRequest quando tiver uma regra Fixa com opção sem valor preenchido
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraFixaComOpcaoSemValorPreenchido(FunctionalTester $I){
        /* Preparação do cenário */
        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
            'idlistadavezregravalor' => $this->cenario['regra.cliente.valor.sim']
        ]);
        // 1.2 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'vendedorfixo' => true,
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando tiver uma regra Estado com opção sem valor preenchido
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraEstadoComOpcaoSemValorPreenchido(FunctionalTester $I){
        /* Preparação do cenário */
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraEstado
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraEstado,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando tiver uma regra Negocio operação com opção sem valor preenchido
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraNegocioOperacaoComOpcaoSemValorPreenchido(FunctionalTester $I){
        /* Preparação do cenário */
        $regraNegocioOperacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '2' // Negócio Operação
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraNegocioOperacao
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraNegocioOperacao,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraNegocioOperacao,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Retornar BadRequest quando tiver uma regra Segmento de Atuação de atuação com opção sem valor preenchido
     * @param FunctionalTester $I
     */
    public function naoSalvarRegraSegmentoAtuacaoComOpcaoSemValorPreenchido(FunctionalTester $I){
        /* Preparação do cenário */
        $regraSegmentoAtuacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '3' // Segmento de atuação
        ]);

        $listaDaVezConfiguracao = [
            'listadavezconfiguracoes' => []
        ];
        // 1 - Pai
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, [], [
            'listadavezconfiguracaonovo' => '1',
            'listadavezregra' => $regraSegmentoAtuacao
        ]);
        // 1.1 - Opção
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.1',
            'idpainovo' => '1',
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'vendedorfixo' => true
        ]);
        // 1.2 - Caso alternativo
        $listaDaVezConfiguracao['listadavezconfiguracoes'][] = $this->getEstruturaListaDaVezConfiguracao($I, $this->cenario, [
            'listadavezconfiguracaonovo' => '1.2',
            'idpainovo' => '1',
            'tiporegistro' => 2,
            'listadavezregra' => $regraSegmentoAtuacao,
            'vendedorfixo' => true
        ]);

        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('POST', 'api/' . $I->tenant . $this->url . 'salvarlote/?grupoempresarial=' . $I->grupoempresarial, $listaDaVezConfiguracao, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */
            $I->deleteFromDatabase('crm.listadavezconfiguracoes', [
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        }
    }

    /**
     * Testa busca de todas as configuracoes de lista da vez
     */
    public function listar(FunctionalTester $I){
        /* Preparação do cenário */
        $dados = [];
        $regraFixa = $I->haveInDatabaseListadaVezRegra($I, [
            'totalvalores' => 1
        ]);
        $regraEstado = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '1' // Estado
        ]);
        $regraNegocioOperacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '2' // Negócio operação
        ]);
        $regraSegmentoAtuacao = $I->haveInDatabaseListadaVezRegra($I, [
            'tipoentidade' => '3' // Segmento de atuação
        ]);

        $estado = $I->haveInDatabaseNsEstados($I, [
            'uf' => 'A1'
        ]);
        $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I);
        $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);;
        $regraFixaValor = $I->haveInDatabaseListadaVezRegraValor($I, [
            'listadavezregra' => $regraFixa,
            'valor' => '1'
        ]);
        $listadavezvendedor = $I->haveInDatabaseListadavezvendedor($I);

        // Insiro configurações no banco
        // 1 - Pai
        $config_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'listadavezregra' => $regraFixa
        ]);
        $dados[] = $config_1;
        // 1.1 - Opção
        $config_1_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraFixa,
            'idlistadavezregravalor' => $regraFixaValor
        ]);
        $dados[] = $config_1_1;
        // 1.1.1 - Regra Estado
        $config_1_1_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1['listadavezconfiguracao'],
            'listadavezregra' => $regraEstado
        ]);
        $dados[] = $config_1_1_1;
        // 1.1.1.1 - Opção
        $config_1_1_1_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraEstado,
            'idestado' => $estado
        ]);
        $dados[] = $config_1_1_1_1;
        // 1.1.1.1.1 - Regra Negócio Operação
        $config_1_1_1_1_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1_1['listadavezconfiguracao'],
            'listadavezregra' => $regraNegocioOperacao
        ]);
        $dados[] = $config_1_1_1_1_1;
        // 1.1.1.1.1.1 - Opção
        $config_1_1_1_1_1_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1_1_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraNegocioOperacao,
            'vendedorfixo' => true,
            'idnegociooperacao' => $negocioOperacao
        ]);
        $dados[] = $config_1_1_1_1_1_1;
        // 1.1.1.1.1.2 - Caso alternativo
        $config_1_1_1_1_1_2 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1_1_1['listadavezconfiguracao'],
            'tiporegistro' => 2,
            'listadavezregra' => $regraNegocioOperacao,
            'listadavezvendedor' => $listadavezvendedor
        ]);
        $dados[] = $config_1_1_1_1_1_2;
        // 1.1.1.2 - Caso alternativo
        $config_1_1_1_2 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1['listadavezconfiguracao'],
            'tiporegistro' => 2,
            'listadavezregra' => $regraEstado,
        ]);
        $dados[] = $config_1_1_1_2;
        // 1.1.1.2.1 - Regra Segmento de atuação
        $config_1_1_1_2_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1_2['listadavezconfiguracao'],
            'listadavezregra' => $regraSegmentoAtuacao
        ]);
        $dados[] = $config_1_1_1_1_1;
        // 1.1.1.2.1.1 - Opção
        $config_1_1_1_2_1_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1_2_1['listadavezconfiguracao'],
            'tiporegistro' => 1,
            'listadavezregra' => $regraSegmentoAtuacao,
            'vendedorfixo' => true,
            'idsegmentoatuacao' => $segmentoAtuacao
        ]);
        $dados[] = $config_1_1_1_2_1_1;
        // 1.1.1.2.1.2 - Caso alternativo
        $config_1_1_1_2_1_2 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $config_1_1_1_2_1['listadavezconfiguracao'],
            'tiporegistro' => 2,
            'listadavezregra' => $regraSegmentoAtuacao,
            'listadavezvendedor' => $listadavezvendedor
        ]);
        $dados[] = $config_1_1_1_2_1_2;

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_getAll($I, $filtros, $dados);
    }
}