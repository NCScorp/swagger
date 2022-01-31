<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Negocios
 */
class NegociosCest {

    private $url_base = '/api/gednasajon/negocios/';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';
    private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 
    /**
     * Guarda as dependências utilizadas no cenário para montar a Entidade
     */
    private $cenario = [];

    /**
     * Adiciona registros de listadavezvendedor ao cenário do teste
     */
    private function montarCenarioListadavezvendedor($I){
        if (!array_key_exists('listadavezvendedor', $this->cenario)){
            $this->cenario['listadavezvendedor'] = $I->haveInDatabaseListadavezvendedor($I);
        }
        if (!array_key_exists('listadavezvendedor.item01', $this->cenario)){
            $this->cenario['listadavezvendedor.item01'] = $I->haveInDatabaseListadavezvendedoritem($I, [
                'listadavezvendedor' => $this->cenario['listadavezvendedor']['listadavezvendedor'],
                'idvendedor' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758',
                'posicao' => 1
            ]);
        }
        if (!array_key_exists('listadavezvendedor.item02', $this->cenario)){
            $this->cenario['listadavezvendedor.item02'] = $I->haveInDatabaseListadavezvendedoritem($I, [
                'listadavezvendedor' => $this->cenario['listadavezvendedor']['listadavezvendedor'],
                'idvendedor' => '4d2b4eab-cd42-469c-a775-8719a2670c87',
                'posicao' => 2
            ]);
        }

        // Insiro outra lista de vendedores
        if (!array_key_exists('listadavezvendedor2', $this->cenario)){
            $this->cenario['listadavezvendedor2'] = $I->haveInDatabaseListadavezvendedor($I);
        }
        if (!array_key_exists('listadavezvendedor2.item01', $this->cenario)){
            $this->cenario['listadavezvendedor2.item01'] = $I->haveInDatabaseListadavezvendedoritem($I, [
                'listadavezvendedor' => $this->cenario['listadavezvendedor2']['listadavezvendedor'],
                'idvendedor' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758',
                'posicao' => 1
            ]);
        }
        if (!array_key_exists('listadavezvendedor2.item02', $this->cenario)){
            $this->cenario['listadavezvendedor2.item02'] = $I->haveInDatabaseListadavezvendedoritem($I, [
                'listadavezvendedor' => $this->cenario['listadavezvendedor2']['listadavezvendedor'],
                'idvendedor' => '4d2b4eab-cd42-469c-a775-8719a2670c87',
                'posicao' => 2
            ]);
        }

        // Insiro outra lista de vendedores
        if (!array_key_exists('listadavezvendedor3', $this->cenario)){
            $this->cenario['listadavezvendedor3'] = $I->haveInDatabaseListadavezvendedor($I);
        }
        if (!array_key_exists('listadavezvendedor3.item01', $this->cenario)){
            $this->cenario['listadavezvendedor3.item01'] = $I->haveInDatabaseListadavezvendedoritem($I, [
                'listadavezvendedor' => $this->cenario['listadavezvendedor3']['listadavezvendedor'],
                'idvendedor' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758',
                'posicao' => 1
            ]);
        }
        if (!array_key_exists('listadavezvendedor3.item02', $this->cenario)){
            $this->cenario['listadavezvendedor3.item02'] = $I->haveInDatabaseListadavezvendedoritem($I, [
                'listadavezvendedor' => $this->cenario['listadavezvendedor3']['listadavezvendedor'],
                'idvendedor' => '4d2b4eab-cd42-469c-a775-8719a2670c87',
                'posicao' => 2
            ]);
        }
    }

    /**
     * Cria no banco de dados as configurações para a regra fixa Já é cliente
     */
    private function haveInDatabaseConfiguracoesRegraFixaJaECliente(FunctionalTester $I, $dados = []){
        // Defino objeto de retorno
        $retorno = [
            'configRegra' => null,
            'configOpcaoJaECliente' => null,
            'configOpcaoNaoECliente' => null
        ];

        // Insiro regras e valores referentes a 'Já é Cliente?' no banco de dados
        if (!array_key_exists('regra.jaecliente', $this->cenario)){
            $this->cenario['regra.jaecliente'] = $I->haveInDatabaseListadaVezRegra($I, [
                'nome' => 'Já é Cliente?',
                'totalvalores' => 2
            ]);
        }
        if (!array_key_exists('regra.jaecliente.valor.nao', $this->cenario)){
            $this->cenario['regra.jaecliente.valor.nao'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.jaecliente'],
                'valor' => '0'
            ]);
        }
        if (!array_key_exists('regra.jaecliente.valor.sim', $this->cenario)){
            $this->cenario['regra.jaecliente.valor.sim'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.jaecliente'],
                'valor' => '1'
            ]);
        }

        // Insiro lista de vendedores e seus itens no banco de dados
        $this->montarCenarioListadavezvendedor($I);

        // Insiro configurações no banco de dados
        // 1 - Pai
        $retorno['configRegra'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => isset($dados['idpai']) ? $dados['idpai'] : null,
            'tiporegistro' => '0',
            'listadavezregra' => $this->cenario['regra.jaecliente']
        ]);
        // 1.1 - Opção Já é cliente
        $retorno['configOpcaoJaECliente'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.jaecliente'],
            'idlistadavezregravalor' => $this->cenario['regra.jaecliente.valor.sim'],
            'vendedorfixo' => true
        ]);
        // 1.2 - Não é cliente
        $retorno['configOpcaoNaoECliente'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.jaecliente'],
            'idlistadavezregravalor' => $this->cenario['regra.jaecliente.valor.nao'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor']
        ]);

        // Retorno dados
        return $retorno;
    }

    /**
     * Cria no banco de dados as configurações para a regra fixa Faturamento Anual
     */
    private function haveInDatabaseConfiguracoesRegraFixaFaturamentoAnual(FunctionalTester $I, $dados = []){
        // Defino objeto de retorno
        $retorno = [
            'configRegra' => null,
            'configOpcao5000000' => null,
            'configOpcao30000000' => null,
            'configFluxoAlternativo' => null
        ];

        // Insiro regras e valores referentes a 'Faturamento Anual' no banco de dados
        if (!array_key_exists('regra.faturamentoanual', $this->cenario)){
            $this->cenario['regra.faturamentoanual'] = $I->haveInDatabaseListadaVezRegra($I, [
                'nome' => 'Faturamento Anual',
                'totalvalores' => 8
            ]);
        }
        if (!array_key_exists('regra.faturamentoanual.valor.5000000', $this->cenario)){
            $this->cenario['regra.faturamentoanual.valor.5000000'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.faturamentoanual'],
                'valor' => '5000000'
            ]);
        }
        if (!array_key_exists('regra.faturamentoanual.valor.30000000', $this->cenario)){
            $this->cenario['regra.faturamentoanual.valor.30000000'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.faturamentoanual'],
                'valor' => '30000000'
            ]);
        }

        // Insiro lista de vendedores e seus itens no banco de dados
        $this->montarCenarioListadavezvendedor($I);

        // Insiro configurações no banco de dados
        // 1 - Pai
        $retorno['configRegra'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => isset($dados['idpai']) ? $dados['idpai'] : null,
            'tiporegistro' => '0',
            'listadavezregra' => $this->cenario['regra.faturamentoanual']
        ]);
        // 1.1 - Opção Até 5 milhões
        $retorno['configOpcao5000000'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.faturamentoanual'],
            'idlistadavezregravalor' => $this->cenario['regra.faturamentoanual.valor.5000000'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor']
        ]);
        // 1.2 - Opção De 5 até 30 milhões
        $retorno['configOpcao30000000'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.faturamentoanual'],
            'idlistadavezregravalor' => $this->cenario['regra.faturamentoanual.valor.30000000'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor2']
        ]);
        // 1.2 - Caso alternativo
        $retorno['configFluxoAlternativo'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '2',
            'listadavezregra' => $this->cenario['regra.faturamentoanual'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor']
        ]);

        // Retorno dados
        return $retorno;
    }

    /**
     * Cria no banco de dados as configurações para a regra fixa Cargo de Contato
     */
    private function haveInDatabaseConfiguracoesRegraFixaCargoDeContato(FunctionalTester $I, $dados = []){
        // Defino objeto de retorno
        $retorno = [
            'configRegra' => null,
            'configOpcaoEstagiario' => null,
            'configOpcaoAnalista' => null,
            'configFluxoAlternativo' => null
        ];

        // Insiro regras e valores referentes a 'Cargo do contato' no banco de dados
        if (!array_key_exists('regra.cargodecontato', $this->cenario)){
            $this->cenario['regra.cargodecontato'] = $I->haveInDatabaseListadaVezRegra($I, [
                'nome' => 'Cargo do Contato',
                'totalvalores' => 8
            ]);
        }
        if (!array_key_exists('regra.cargodecontato.valor.estagiario', $this->cenario)){
            $this->cenario['regra.cargodecontato.valor.estagiario'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.cargodecontato'],
                'valor' => 'Estagiario'
            ]);
        }
        if (!array_key_exists('regra.cargodecontato.valor.analista', $this->cenario)){
            $this->cenario['regra.cargodecontato.valor.analista'] = $I->haveInDatabaseListadaVezRegraValor($I, [
                'listadavezregra' => $this->cenario['regra.cargodecontato'],
                'valor' => 'Analista'
            ]);
        }

        // Insiro lista de vendedores e seus itens no banco de dados
        $this->montarCenarioListadavezvendedor($I);

        // Insiro configurações no banco de dados
        // 1 - Pai
        $retorno['configRegra'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => isset($dados['idpai']) ? $dados['idpai'] : null,
            'tiporegistro' => '0',
            'listadavezregra' => $this->cenario['regra.cargodecontato']
        ]);
        // 1.1 - Opção Estagiario
        $retorno['configOpcaoEstagiario'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.cargodecontato'],
            'idlistadavezregravalor' => $this->cenario['regra.cargodecontato.valor.estagiario'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor'],
            'ordem' => 2
        ]);
        // 1.2 - Opção Analista
        $retorno['configOpcaoAnalista'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.cargodecontato'],
            'idlistadavezregravalor' => $this->cenario['regra.cargodecontato.valor.analista'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor2'],
            'ordem' => 1
        ]);
        // 1.2 - Caso alternativo
        $retorno['configFluxoAlternativo'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '2',
            'listadavezregra' => $this->cenario['regra.cargodecontato'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor']
        ]);

        // Retorno dados
        return $retorno;
    }

    /**
     * Cria no banco de dados as configurações para a regra UF
     */
    private function haveInDatabaseConfiguracoesRegraUF(FunctionalTester $I, $dados = []){
        // Defino objeto de retorno
        $retorno = [
            'configRegra' => null,
            'configOpcaoE1' => null,
            'configOpcaoE2' => null,
            'configFluxoAlternativo' => null
        ];

        // Insiro regra no banco de dados
        if (!array_key_exists('regra.uf', $this->cenario)){
            $this->cenario['regra.uf'] = $I->haveInDatabaseListadaVezRegra($I, [
                'tipoentidade' => '1' // Estado
            ]);
        }

        // Insiro uf no banco de dados
        if (!array_key_exists('uf1', $this->cenario)){
            $this->cenario['uf1'] = $I->haveInDatabaseNsEstados($I, [
                'uf' => 'E1'
            ]);
        }
        if (!array_key_exists('uf2', $this->cenario)){
            $this->cenario['uf2'] = $I->haveInDatabaseNsEstados($I, [
                'uf' => 'E2'
            ]);
        }
        if (!array_key_exists('uf3', $this->cenario)){
            $this->cenario['uf3'] = $I->haveInDatabaseNsEstados($I, [
                'uf' => 'E3'
            ]);
        }

        // Insiro lista de vendedores e seus itens no banco de dados
        $this->montarCenarioListadavezvendedor($I);

        // Insiro configurações no banco de dados
        // 1 - Pai
        $retorno['configRegra'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => isset($dados['idpai']) ? $dados['idpai'] : null,
            'tiporegistro' => '0',
            'listadavezregra' => $this->cenario['regra.uf']
        ]);
        // 1.1 - Opção
        $retorno['configOpcaoE1'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.uf'],
            'idestado' => $this->cenario['uf1'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor'],
            'ordem' => 2
        ]);
        // 1.2 - Opção
        $retorno['configOpcaoE2'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.uf'],
            'idestado' => $this->cenario['uf2'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor2'],
            'ordem' => 1
        ]);
        // 1.2 - Caso alternativo
        $retorno['configFluxoAlternativo'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '2',
            'listadavezregra' => $this->cenario['regra.uf'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor3']
        ]);

        // Retorno dados
        return $retorno;
    }

    /**
     * Cria no banco de dados as configurações para a regra Negócio Operação
     */
    private function haveInDatabaseConfiguracoesRegraNegocioOperacao(FunctionalTester $I, $dados = []){
        // Defino objeto de retorno
        $retorno = [
            'configRegra' => null,
            'configOpcaoOperacao1' => null,
            'configOpcaoOperacao2' => null,
            'configFluxoAlternativo' => null
        ];

        // Insiro regra no banco de dados
        if (!array_key_exists('regra.negociooperacao', $this->cenario)){
            $this->cenario['regra.negociooperacao'] = $I->haveInDatabaseListadaVezRegra($I, [
                'tipoentidade' => '2' // Negócio Operação
            ]);
        }

        // Insiro negócio operação no banco de dados
        if (!array_key_exists('negociooperacao1', $this->cenario)){
            $this->cenario['negociooperacao1'] = $I->haveInDatabaseNegocioOperacao($I, [
                'codigo' => 'TST1'
            ]);
        }
        if (!array_key_exists('negociooperacao2', $this->cenario)){
            $this->cenario['negociooperacao2'] = $I->haveInDatabaseNegocioOperacao($I, [
                'codigo' => 'TST2'
            ]);
        }

        // Insiro lista de vendedores e seus itens no banco de dados
        $this->montarCenarioListadavezvendedor($I);

        // Insiro configurações no banco de dados
        // 1 - Pai
        $retorno['configRegra'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => isset($dados['idpai']) ? $dados['idpai'] : null,
            'tiporegistro' => '0',
            'listadavezregra' => $this->cenario['regra.negociooperacao']
        ]);
        // 1.1 - Opção
        $retorno['configOpcaoOperacao1'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.negociooperacao'],
            'id_negociooperacao' => $this->cenario['negociooperacao1'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor'],
            'ordem' => 2
        ]);
        // 1.2 - Opção
        $retorno['configOpcaoOperacao2'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.negociooperacao'],
            'id_negociooperacao' => $this->cenario['negociooperacao2'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor2'],
            'ordem' => 1
        ]);
        // 1.2 - Caso alternativo
        $retorno['configFluxoAlternativo'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '2',
            'listadavezregra' => $this->cenario['regra.negociooperacao'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor']
        ]);

        // Retorno dados
        return $retorno;
    }

    /**
     * Cria no banco de dados as configurações para a regra Negócio Operação
     */
    private function haveInDatabaseConfiguracoesRegraSegmentoAtuacao(FunctionalTester $I, $dados = []){
        // Defino objeto de retorno
        $retorno = [
            'configRegra' => null,
            'configOpcaoSegmento1' => null,
            'configOpcaoSegmento2' => null,
            'configFluxoAlternativo' => null
        ];

        // Insiro regra no banco de dados
        if (!array_key_exists('regra.segmentoatuacao', $this->cenario)){
            $this->cenario['regra.segmentoatuacao'] = $I->haveInDatabaseListadaVezRegra($I, [
                'tipoentidade' => '3' // Segmento de atuação
            ]);
        }

        // Insiro negócio operação no banco de dados
        if (!array_key_exists('segmentoatuacao1', $this->cenario)){
            $this->cenario['segmentoatuacao1'] = $I->haveInDatabaseSegmentoAtuacao($I, [
                'codigo' => 'TST1'
            ]);
        }
        if (!array_key_exists('segmentoatuacao2', $this->cenario)){
            $this->cenario['segmentoatuacao2'] = $I->haveInDatabaseSegmentoAtuacao($I, [
                'codigo' => 'TST2'
            ]);
        }
        if (!array_key_exists('segmentoatuacao3', $this->cenario)){
            $this->cenario['segmentoatuacao3'] = $I->haveInDatabaseSegmentoAtuacao($I, [
                'codigo' => 'TST3'
            ]);
        }

        // Insiro lista de vendedores e seus itens no banco de dados
        $this->montarCenarioListadavezvendedor($I);

        // Insiro configurações no banco de dados
        // 1 - Pai
        $retorno['configRegra'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => isset($dados['idpai']) ? $dados['idpai'] : null,
            'tiporegistro' => '0',
            'listadavezregra' => $this->cenario['regra.segmentoatuacao']
        ]);
        // 1.1 - Opção
        $retorno['configOpcaoSegmento1'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.segmentoatuacao'],
            'id_segmentoatuacao' => $this->cenario['segmentoatuacao1'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor'],
            'ordem' => 2
        ]);
        // 1.2 - Opção
        $retorno['configOpcaoSegmento2'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '1',
            'listadavezregra' => $this->cenario['regra.segmentoatuacao'],
            'id_segmentoatuacao' => $this->cenario['segmentoatuacao2'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor2'],
            'ordem' => 1
        ]);
        // 1.2 - Caso alternativo
        $retorno['configFluxoAlternativo'] = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
            'idpai' => $retorno['configRegra']['listadavezconfiguracao'],
            'tiporegistro' => '2',
            'listadavezregra' => $this->cenario['regra.segmentoatuacao'],
            'listadavezvendedor' => $this->cenario['listadavezvendedor']
        ]);

        // Retorno dados
        return $retorno;
    }

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::NEGOCIOS_INDEX, EnumAcao::NEGOCIOS_GET, EnumAcao::NEGOCIOS_CREATE, EnumAcao::NEGOCIOS_PUT, EnumAcao::NEGOCIOS_DELETE, EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO, EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO]);
    $this->cenario = [];
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('crm.historicoatcs');
    $I->deleteAllFromDatabase('crm.atcs');
    $I->deleteAllFromDatabase('crm.historicosnegocios');
    $I->deleteAllFromDatabase('crm.negociospropostasvendedores');
    $I->deleteAllFromDatabase('crm.negociostelefones');
    $I->deleteAllFromDatabase('crm.negocioscontatos');
    $I->deleteAllFromDatabase('crm.negocios');
    $I->deleteAllFromDatabase('crm.midiasorigem');
  }
  /**
   * @param FunctionalTester $I
   */
  public function criaNegocio(FunctionalTester $I)
  {
    /* inicializações */
    $midia = $I->haveInDatabaseMidia($I);
    $operacao = $I->haveInDatabaseNegocioOperacao($I);
    $tipoAcionamento = $I->haveInDatabaseTipoAcionamento($I);
    unset($tipoAcionamento['tenant']); //Não é usado

    $negocio = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'operacao' => ['proposta_operacao' => $operacao['proposta_operacao']],
      //'numero' => "1", Agora é automático
      'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
      'cliente' => null,//['cliente' => $cliente['cliente']],
      'codigodepromocao' => null, //object
      'midiaorigem' => ['midia' => $midia['midiaorigem']],
      'cliente_codigo' => "1",
      'cliente_companhia' => "Companhia",
      'cliente_nomefantasia' => "Nome Fantasia Cliente",
      'cliente_qualificacao' => "1",
      'cliente_documento' => "Documento Cliente",
      'cliente_email' => "email cliente",
      'cliente_site' => "site cliente",
      'cliente_captador' => null, //object
      'cliente_segmentodeatuacao' => null,//object
      'cliente_receitaanual' => 5000000,
      'uf' => "RJ",
      'segmentodeatuacao' => null,//object
      'prenegocio' => 1,
      'ehcliente' => 1,
      'observacao' => "obs",
      'cliente_municipioibge' => 00000000,
      'tipodeacionamento' => $tipoAcionamento
    ];

    /* execução da funcionalidade */
    $negocio_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $negocio['documento'] = $negocio_criado['documento']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $negocioCheckBanco = ['documento' =>$negocio['documento']];
    $I->canSeeInDatabase('crm.negocios', $negocioCheckBanco);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaNegocioComContatoETelefone(FunctionalTester $I)
  {
    /* inicializações */
    $midia = $I->haveInDatabaseMidia($I);
    $operacao = $I->haveInDatabaseNegocioOperacao($I);
    $nome_contato = 'Nome do contato 2';
    $telefone = '12344321';

    $negocio = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'operacao' => ['proposta_operacao' => $operacao['proposta_operacao']],
      'numero' => "2",
      'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
      'cliente' => null,//['cliente' => $cliente['cliente']],
      'codigodepromocao' => null, //object
      'midiaorigem' => ['midia' => $midia['midiaorigem']],
      'cliente_codigo' => "1",
      'cliente_companhia' => "Companhia 2",
      'cliente_nomefantasia' => "Nome Fantasia Cliente 2",
      'cliente_qualificacao' => "1",
      'cliente_documento' => "Documento Cliente 2",
      'cliente_email' => "email cliente 2",
      'cliente_site' => "site cliente 2",
      'cliente_captador' => null, //object
      'cliente_segmentodeatuacao' => null,//object
      'cliente_receitaanual' => 5000000,
      'uf' => "RJ",
      'segmentodeatuacao' => null,//object
      'prenegocio' => 1,
      'ehcliente' => 1,
      'observacao' => "obs 2",
      'cliente_municipioibge' => 00000000,
      "negocioscontatos" => [
        [
          'tenant' => $this->tenant_numero,
          'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
          "nome" => $nome_contato,
          "sobrenome" => 'Sobrenome do contato 2',
          "cargo" => "Sócio/Proprietário/CEO",
          "email" => 'email@do.contato2',
          "ddi" => '55',
          "ddd" => '21',
          "telefone" => $telefone,
          "ramal" => '1'
        ]
      ]
    ];

    /* execução da funcionalidade */
    $negocio_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $negocio['documento'] = $negocio_criado['documento'];
    $negocioCheckBanco = ['documento' =>$negocio['documento']];

    $I->canSeeInDatabase('crm.negocios', $negocioCheckBanco);
    $I->canSeeInDatabase('crm.negocioscontatos', ['id_negocio' => $negocio_criado['documento'], 'nome' => $nome_contato, 'telefone' => $telefone]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaNegocio(FunctionalTester $I)
  {
    /* inicializações */
    $negocio = $I->haveInDatabaseNegocio($I, [
        'uf' => ['uf' => 'RJ'],
        'clientemunicipioibge' => ['codigo' => '2511905']
    ]);

    $negocio['observacao'] .= ' complemento observacao';
    /* execução da funcionalidade */
    $response = $I->sendRaw('PUT', $this->url_base . $negocio['documento'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioCheckBanco = [
      'documento' => $negocio['documento'],
      'observacao' => $negocio['observacao'],
    ];
    $I->canSeeInDatabase('crm.negocios', $negocioCheckBanco);
  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiNegocio(FunctionalTester $I)
  {
    /* inicializações */
    $negocio = $I->haveInDatabaseNegocio($I, [
        'uf' => ['uf' => 'RJ'],
        'clientemunicipioibge' => ['codigo' => '2511905']
    ]);

    /* execução da funcionalidade */
    $response = $I->sendRaw('DELETE', $this->url_base . $negocio['documento'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioCheckBanco = [
      'documento' => $negocio['documento'],
    ];
    $I->cantSeeInDatabase('crm.negocios', $negocioCheckBanco);
  }

  public function qualificaNegocio (FunctionalTester $I){

    $negocio = $I->haveInDatabaseNegocio($I);
    $guidVendedor = 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'; //guid do vendedor no dump.sql - ns.pessoas

    $dadosQualificacao = [
      "cliente" => ['cliente' => '460f64b5-e296-4ec6-8833-b93edd9310a7'],
      "dataqualificacao_pn" => date('Y-m-d'),
      "vendedor" => ['vendedor_id' => $guidVendedor],
      "periodoqualificacao_pn" => "1",
      "mensagemqualificacao_pn" => "mensagem de qualificação",
    ];
    $response = $I->sendRaw('POST', $this->url_base . $negocio['documento'].'/preNegocioQualificar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $dadosQualificacao, [], [], null);
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioCheckBanco = [
      'documento' => $negocio['documento'],
      'periodoqualificacao_pn' => $dadosQualificacao['periodoqualificacao_pn'],
    ];
    $I->canSeeInDatabase('crm.negocios', $negocioCheckBanco);
  }

  public function qualificaNegocioSemCliente (FunctionalTester $I){

    $negocio = $I->haveInDatabaseNegocio($I);
    $guidVendedor = 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'; //guid do vendedor no dump.sql - ns.pessoas

    $dadosQualificacao = [
      "dataqualificacao_pn" => date('Y-m-d'),
      "vendedor" => ['vendedor_id' => $guidVendedor],
      "periodoqualificacao_pn" => "1",
      "mensagemqualificacao_pn" => "mensagem de qualificação",
    ];
    $response = $I->sendRaw('POST', $this->url_base . $negocio['documento'].'/preNegocioQualificar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $dadosQualificacao, [], [], null);
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioCheckBanco = [
      'documento' => $negocio['documento'],
      'periodoqualificacao_pn' => $dadosQualificacao['periodoqualificacao_pn'],
    ];
    $I->canSeeInDatabase('crm.negocios', $negocioCheckBanco);

    //ver o prospect criado
    $prospectCheckBanco = [
      'nomefantasia' => $negocio['cliente_nomefantasia'],
      'nome' => $negocio['cliente_companhia'],
      'pessoa' => $negocio['cliente_documento'],
      'cnpj' => $negocio['cliente_documento']
    ];
    $I->canSeeInDatabase('ns.vw_prospects', $prospectCheckBanco);
  }

    /**
     * Testa qualificação do negócio passando lista da vez
     */
    public function qualificaNegocioComListaDaVez (FunctionalTester $I){
        /* Preparação do cenário */
        // Lista de vendedores
        $listadavezvendedor = $I->haveInDatabaseListadavezvendedor($I);
        // Itens da lista de vendedores
        $listadavezvendedoritem01 = $I->haveInDatabaseListadavezvendedoritem($I, [
            'listadavezvendedor' => $listadavezvendedor['listadavezvendedor'],
            'idvendedor' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'
        ]);
        $listadavezvendedoritem02 = $I->haveInDatabaseListadavezvendedoritem($I, [
            'listadavezvendedor' => $listadavezvendedor['listadavezvendedor'],
            'idvendedor' => '4d2b4eab-cd42-469c-a775-8719a2670c87',
            'posicao' => 2
        ]);
        // Negócio
        $negocio = $I->haveInDatabaseNegocio($I);

        /* Execução da funcionalidade */
        $dados = [
            "cliente" => [
                'cliente' => '460f64b5-e296-4ec6-8833-b93edd9310a7'
            ],
            "dataqualificacao_pn" => date('Y-m-d'),
            "vendedor" => [
                'vendedor_id' => $listadavezvendedoritem01['id_vendedor']
            ],
            "periodoqualificacao_pn" => "1",
            "mensagemqualificacao_pn" => "mensagem de qualificação",
            "listadavezvendedor" => $listadavezvendedor['listadavezvendedor'],
            "listadavezvendedoritem" => $listadavezvendedoritem01['listadavezvendedoritem']
        ];

        $response = $I->sendRaw('POST', $this->url_base . $negocio['documento'].'/preNegocioQualificar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $dados, [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            // Verifico se a informação de qualificação foi setada no negócio
            $I->canSeeInDatabase('crm.negocios', [
                'documento' => $negocio['documento'],
                'periodoqualificacao_pn' => $dados['periodoqualificacao_pn']
            ]);
            // Verifico se o posicionamento dos vendedores na lista da vez está correto
            $I->canSeeInDatabase('crm.listadavezvendedoresitens', [
                'listadavezvendedoritem' => $listadavezvendedoritem01['listadavezvendedoritem'],
                'posicao' => '2',
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
            $I->canSeeInDatabase('crm.listadavezvendedoresitens', [
                'listadavezvendedoritem' => $listadavezvendedoritem02['listadavezvendedoritem'],
                'posicao' => '1',
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            /* Remove dado criado no banco */

            // Removo vendedor de negócios propostas vendedores
            $I->deleteFromDatabase('crm.negociospropostasvendedores', [
                'documento' => $negocio['documento'],
                'id_vendedor' => $dados['vendedor']['vendedor_id'],
                'tenant' => $I->tenant_numero,
                'id_grupoempresarial' => $I->id_grupoempresarial
            ]);

            // Removo follow up
            $I->deleteFromDatabase('ns.followups', [
                'tenant' => $I->tenant_numero,
                'proposta' => $negocio['documento']
            ]);
        }
    }

  public function desqualificaNegocio (FunctionalTester $I){

    $motivoDesqualificacao = $I->haveInDatabaseMotivoDesqualificacao($I);
    $negocio = $I->haveInDatabaseNegocio($I);
    $guidVendedor = 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'; //guid do vendedor no dump.sql - ns.pessoas

    $dadosDesqualificacao = [
      'motivodesqualificacao_pn' => ['motivodesqualificacaoprenegocio' => $motivoDesqualificacao['motivodesqualificacaoprenegocio']],
      'observacaodesqualificacao_pn' => "observacao",
    ];
    $response = $I->sendRaw('POST', $this->url_base . $negocio['documento'].'/preNegocioDesqualificar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $dadosDesqualificacao, [], [], null);
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioCheckBanco = [
      'documento' => $negocio['documento'],
      'observacaodesqualificacao_pn' => $dadosDesqualificacao['observacaodesqualificacao_pn'],
    ];
    $I->canSeeInDatabase('crm.negocios', $negocioCheckBanco);
  }

    /**
     * Testa busca da lista da vez, validando a regra fixa Já é cliente ?
     */
    public function buscaListaDaVezRegraFixaJaEClienteOpcaoNao(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraFixaJaECliente($I);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'ehcliente' => false
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra fixa Já é cliente ?
     */
    public function buscaListaDaVezRegraFixaJaEClienteOpcaoSim(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraFixaJaECliente($I);
        $cliente = $I->haveInDatabaseCliente($I, [
            'vendedor' => [
                'idvendedor' => '4d2b4eab-cd42-469c-a775-8719a2670c87'
            ]
        ]);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'ehcliente' => true,
            'id_cliente' => $cliente
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($cliente['vendedor']['idvendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals(null, $response['listadavezvendedoritem']);
            $I->assertEquals(null, $response['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra fixa Faturamento Anual para o valor 30000000
     */
    public function buscaListaDaVezRegraFixaFaturamentoAnualOpcao30000000(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraFixaFaturamentoAnual($I);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'cliente_receitaanual' => '30000000'
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra fixa Faturamento Anual para o fluxo alternativo
     */
    public function buscaListaDaVezRegraFixaFaturamentoAnualFluxoAlternativo(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraFixaFaturamentoAnual($I);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'cliente_receitaanual' => '100000000'
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra fixa Cargo de contato para Analista
     */
    public function buscaListaDaVezRegraFixaCargoDeContatoOpcaoAnalista(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraFixaCargoDeContato($I);
        $negocio = $I->haveInDatabaseNegocio($I);
        $negocioContato = $I->haveInDatabaseNegocioContato($I, [
            'negocio' => $negocio,
            'cargo' => 'Analista'
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra fixa Cargo de contato para Analista e Estagiário, 
     *  onde deve validar a opção pela ordem correta
     */
    public function buscaListaDaVezRegraFixaCargoDeContatoOpcaoEstagiarioEAnalista(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraFixaCargoDeContato($I);
        $negocio = $I->haveInDatabaseNegocio($I);
        $negocioContato = $I->haveInDatabaseNegocioContato($I, [
            'negocio' => $negocio,
            'cargo' => 'Analista'
        ]);
        $negocioContato = $I->haveInDatabaseNegocioContato($I, [
            'negocio' => $negocio,
            'cargo' => 'Estagiario'
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra Uf
     */
    public function buscaListaDaVezRegraUF(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraUF($I);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'uf' => $this->cenario['uf2'],
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra Área de Operação
     */
    public function buscaListaDaVezRegraNegocioOperacao(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraNegocioOperacao($I);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'operacao' => $this->cenario['negociooperacao2'],
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando a regra Segmento de atuação
     */
    public function buscaListaDaVezRegraSegmentoAtuacao(FunctionalTester $I){
        /* Preparação do cenário */
        $this->haveInDatabaseConfiguracoesRegraSegmentoAtuacao($I);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'cliente_segmentodeatuacao' => $this->cenario['segmentoatuacao2'],
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando árvore de dois níveis de regras
     */
    public function buscaListaDaVezRegraSegmentoAtuacaoEUf(FunctionalTester $I){
        /* Preparação do cenário */
        $configSegmento = $this->haveInDatabaseConfiguracoesRegraSegmentoAtuacao($I);
        $configUF = $this->haveInDatabaseConfiguracoesRegraUF($I, [
            'idpai' => $configSegmento['configOpcaoSegmento1']['listadavezconfiguracao']
        ]);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'uf' => $this->cenario['uf2'],
            'cliente_segmentodeatuacao' => $this->cenario['segmentoatuacao1'],
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor2.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor2']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }

    /**
     * Testa busca da lista da vez, validando árvore de dois níveis de regras, com o segundo nível acessado por um fluxo alternativo
     */
    public function buscaListaDaVezRegraSegmentoAtuacaoComFluxoAlternativoEUf(FunctionalTester $I){
        /* Preparação do cenário */
        $configSegmento = $this->haveInDatabaseConfiguracoesRegraSegmentoAtuacao($I);
        $configUF = $this->haveInDatabaseConfiguracoesRegraUF($I, [
            'idpai' => $configSegmento['configFluxoAlternativo']['listadavezconfiguracao']
        ]);
        $negocio = $I->haveInDatabaseNegocio($I, [
            'uf' => $this->cenario['uf3'],
            'cliente_segmentodeatuacao' => $this->cenario['segmentoatuacao3'],
        ]);

        /* Execução da funcionalidade */
        $response = $I->sendRaw('GET', $this->url_base . $negocio['documento'].'/buscar-vendedor-da-lista-da-vez?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* Validação do resultado */
        try {
            $I->canSeeResponseCodeIs(HttpCode::OK);
            $I->assertEquals($this->cenario['listadavezvendedor3.item01']['id_vendedor'], $response['vendedor']['vendedor_id']);
            $I->assertEquals($this->cenario['listadavezvendedor3.item01']['listadavezvendedoritem'], $response['listadavezvendedoritem']);
            $I->assertEquals($this->cenario['listadavezvendedor3']['listadavezvendedor'], $response['listadavezvendedor']['listadavezvendedor']);
        } catch (\Exception $e) {
            throw $e;
        } finally { }
    }
}
