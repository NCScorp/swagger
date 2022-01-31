<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Configuração da lista da vez
 */
class RelatoriosPainelMarketingCest {
    /**
     * Url base de requisições do teste.
     */
    private $url = '/paineis/marketing/';
    
    /**
     * Guarda as dependências utilizadas no cenário para montar a Entidade
     */
    private $cenario = [];

    // Constantes referente aos tipos de classificadores
    const PMCE_CAMPANHAORIGEM = 0;
    const PMCE_MIDIAORIGEM = 1;
    const PMCE_TIPOACIONAMENTO = 2;
    const PMCE_ESTABELECIMENTO = 3;
    const PMCE_AREANEGOCIO = 4;
    const PMCE_ATRIBUICAO = 5;
    const PMCE_SEGMENTOATUACAO = 6;
    const PMCE_FATURAMENTOANUAL = 7;
    const PMCE_EHCLIENTE = 8;
    const PMCE_CARGO = 9;
    const PMCE_QUALIFICADOEM = 10;
    const PMCE_MOTIVODESQUALIFICACAO = 11;

    // Constantes referentes aos cargos do contato do negócio
    const CCN_SOCIO_PROPRIETARIO_CEO = 'Sócio/Proprietário/CEO';
    const CCN_DIRETOR = 'Diretor';

    /**
     * Busca painel de marketing. Será utilizada para todos os testes de filtros.
     */
    private function _get(FunctionalTester $I, $filtros = [], $painelEsperado = [], $statusEsperado = HttpCode::OK){
        /* Execução da funcionalidade */
        $retorno = $I->sendRaw('GET', 'api/' . $I->tenant . $this->url . '?' . http_build_query($filtros), [], [], [], null);
        
        /* Validação do resultado */
        $I->canSeeResponseCodeIs($statusEsperado);

        // Se for o status de OK, faço validações do painel
        if ($statusEsperado == HttpCode::OK) {
            /** VALIDAÇÃO DOS MESES DO PAINEL */
            // Valido se o retorno tem a mesma quantidade de meses que o esperado
            $I->assertEquals(count($painelEsperado['meses']), count($retorno['meses']));

            // Para cada registro de mês, faço as validações internas
            foreach ($painelEsperado['meses'] as $indexMes => $painelMes) {
                $painelRetornoMes = $retorno['meses'][$indexMes];

                // Valido se o AnoMes está em ordem
                $I->assertEquals($painelMes['anomes'], $painelRetornoMes['anomes']);
                // Valido total de registros
                $I->assertEquals($painelMes['negociostotais'], $painelRetornoMes['negociostotais']);
                // Valido que o mês retornado tem a mesma quantidade de meses de qualificação que o esperado
                $I->assertEquals(count($painelMes['listaqualificacao']), count($painelRetornoMes['listaqualificacao']));

                // Para cada registro de qualificacao do mês, faço as validações internas
                foreach ($painelMes['listaqualificacao'] as $indexMesQualificacao => $painelMesQualificacao) {
                    $painelRetornoMesQualificacao = $painelRetornoMes['listaqualificacao'][$indexMesQualificacao];

                    // Valido se o AnoMes está em ordem
                    $I->assertEquals($painelMesQualificacao['anomes'], $painelRetornoMesQualificacao['anomes']);
                    // Valido total de registros qualificados
                    $I->assertEquals($painelMesQualificacao['negociosqualificados'], $painelRetornoMesQualificacao['negociosqualificados']);
                    // Valido total de registros desqualificados
                    $I->assertEquals($painelMesQualificacao['negociosdesqualificados'], $painelRetornoMesQualificacao['negociosdesqualificados']);
                }
            }

            /** VALIDAÇÃO DOS CLASSIFICADORES DO PAINEL */
            // Valido se o retorno tem a mesma quantidade de classificadores que o esperado
            $I->assertEquals(count($painelEsperado['classificadores']), count($retorno['classificadores']));

            // Para cada classificador, faço as validações internas
            foreach ($painelEsperado['classificadores'] as $classificador) {
                //Uso um array_values somente para reorganizar os ID's, já que o array_filter nem sempre retorna o id 0 mesmo contendo um valor.
                $arrRetorno = array_values( array_filter($retorno['classificadores'], function($classificadorRetorno) use ($classificador) {
                    return (
                        $classificador['entidade'] == $classificadorRetorno['entidade'] // Mesmo tipo de classificador
                        && $classificador['id'] == $classificadorRetorno['id']
                    );
                }) );

                // Valido que o classificador está presente no retorno
                $I->assertEquals(1, count($arrRetorno));
                $classificadorRetorno = $arrRetorno[0];
                // Valido se tem a mesma quantidade de prenegocios
                $I->assertEquals(count($classificador['prenegocios']), count($classificadorRetorno['prenegocios']));
                // Valido se tem a mesma quantidade de negocios qualificados
                $I->assertEquals(count($classificador['negociosqualificados']), count($classificadorRetorno['negociosqualificados']));
                // Valido se tem a mesma quantidade de negocios desqualificados
                $I->assertEquals(count($classificador['negociosdesqualificados']), count($classificadorRetorno['negociosdesqualificados']));
                // Valido se os pré negócios estão preenchidos
                foreach ($classificador['prenegocios'] as $negocio) {
                    $negocioRetornoIndex = array_search($negocio, $classificadorRetorno['prenegocios']);
                    $I->assertGreaterThan(-1, $negocioRetornoIndex);
                }
                // Valido se os negócios qualificados preenchidos
                foreach ($classificador['negociosqualificados'] as $negocio) {
                    $negocioRetornoIndex = array_search($negocio, $classificadorRetorno['negociosqualificados']);
                    $I->assertGreaterThan(-1, $negocioRetornoIndex);
                }
                // Valido se os negócios qualificados preenchidos
                foreach ($classificador['negociosdesqualificados'] as $negocio) {
                    $negocioRetornoIndex = array_search($negocio, $classificadorRetorno['negociosdesqualificados']);
                    $I->assertGreaterThan(-1, $negocioRetornoIndex);
                }
            }
        }
    
        return $retorno;
    }

    /**
     * Função que roda antes de qualquer teste
     */
    public function _before(FunctionalTester $I) {
        // Faz o mock do usuário e suas permissões
        $I->amSamlLoggedInAs('colaborador@empresa.com.br', [
            EnumAcao::RELATORIOS_PAINEL_MARKETING
        ]);
    }

    /**
     * Testa busca do painel de marketing
     */
    public function get(FunctionalTester $I){
        /* Preparação do cenário */
        // Crio requisitos do negócio
        $campanhaOrigem1 = $I->haveInDatabasePromocaoLead($I);
        $campanhaOrigem2 = $I->haveInDatabasePromocaoLead($I, [
            'codigo' => 2
        ]);
        $situacaoPrenegocio = $I->haveInDatabaseSituacoesprenegocios($I);
        $operacao1 = $I->haveInDatabaseNegocioOperacao($I);
        $midia1 = $I->haveInDatabaseMidia($I);
        $tipoAcionamento1 = $I->haveInDatabaseTipoAcionamento($I);
        $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';
        $vendedor = [
            'vendedor_id' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'
        ];
        $segmentoatuacao = $I->haveInDatabaseSegmentoAtuacao($I);
        $faturamentoanual = '30000000';
        $motivoDesqualificacao = $I->haveInDatabaseMotivoDesqualificacao($I);

        // Crio negócios
        // Negócio qualificado
        $negocio1 = $I->haveInDatabaseNegocio($I, [
            'created_at' => '2020-07-30',
            'id_codigodepromocao' => $campanhaOrigem1,
            'situacaoprenegocio' => $situacaoPrenegocio,
            'operacao' => $operacao1,
            'midia' => $midia1,
            'tipoacionamento' => $tipoAcionamento1,
            'estabelecimento' => $estabelecimento,
            'cliente_captador' => $vendedor,
            'cliente_segmentodeatuacao' => $segmentoatuacao,
            'cliente_receitaanual' => $faturamentoanual,
            'ehcliente' => true,
            'tipoqualificacao_pn' => 1, // Qualificado
            'created_at_qualificacao_pn' => '2020-08-01'
        ]);

        // Contatos do negócio 1
        $negocio1Contato1 = $I->haveInDatabaseNegocioContato($I, [
            'negocio' => $negocio1,
            'cargo' => self::CCN_SOCIO_PROPRIETARIO_CEO
        ]);
        $negocio1Contato2 = $I->haveInDatabaseNegocioContato($I, [
            'negocio' => $negocio1,
            'cargo' => self::CCN_DIRETOR
        ]);

        // Pré negócio
        $negocio2 = $I->haveInDatabaseNegocio($I, [
            'created_at' => '2020-07-30',
            'id_codigodepromocao' => $campanhaOrigem2,
            'situacaoprenegocio' => $situacaoPrenegocio,
            'operacao' => $operacao1,
            'midia' => $midia1,
            'tipoacionamento' => $tipoAcionamento1,
            'estabelecimento' => $estabelecimento,
            'cliente_captador' => $vendedor,
            'cliente_segmentodeatuacao' => $segmentoatuacao,
            'cliente_receitaanual' => $faturamentoanual,
            'ehcliente' => true,
        ]);

        // Contatos do negócio 2
        $negocio2Contato1 = $I->haveInDatabaseNegocioContato($I, [
            'negocio' => $negocio2,
            'cargo' => self::CCN_DIRETOR
        ]);

        // Negócio desqualificado
        $negocio3 = $I->haveInDatabaseNegocio($I, [
            'created_at' => '2020-08-23',
            'situacaoprenegocio' => $situacaoPrenegocio,
            'operacao' => $operacao1,
            'midia' => $midia1,
            'tipoacionamento' => $tipoAcionamento1,
            'estabelecimento' => $estabelecimento,
            'cliente_captador' => $vendedor,
            'cliente_segmentodeatuacao' => $segmentoatuacao,
            'cliente_receitaanual' => $faturamentoanual,
            'ehcliente' => false,
            'tipoqualificacao_pn' => 2, // Desqualificado
            'created_at_qualificacao_pn' => '2020-08-24',
            'id_motivodesqualificacao_pn' => $motivoDesqualificacao['motivodesqualificacaoprenegocio'] // Motivo da desqualificação
        ]);

        // Defino meses de retorno
        $arrMeses = [
            '202007', '202008', '202009', '202010', '202011', '202012',
            '202101', '202102', '202103', '202104', '202105', '202106',
        ];

        $painel = [
            'meses' => [],
            'classificadores' => []
        ];

        /** DADOS DE MESES ESPERADOS */
        // Monto retorno esperado dos meses do painel
        foreach ($arrMeses as $indexMes => $mes) {
            $painelMes = [
                'anomes' => $mes,
                'negociostotais' => 0,
                'listaqualificacao' => []
            ];

            foreach ($arrMeses as $indexMesQualificacao => $mesQualificacao) {
                if ($indexMesQualificacao >= $indexMes) {
                    $painelMesQualificacao = [
                        'anomes' => $mesQualificacao,
                        'negociosqualificados' => 0,
                        'negociosdesqualificados' => 0,
                    ];
    
                    $painelMes['listaqualificacao'][] = $painelMesQualificacao;
                }
            }

            $painel['meses'][] = $painelMes;
        }

        // Altero dados esperados do mês 202007
        $painel['meses'][0]['negociostotais'] = 2;
        // Altero dados esperados do mês 202007, mês de qualificacao 202008
        $painel['meses'][0]['listaqualificacao'][1]['negociosqualificados'] = 1;

        // Altero dados esperados do mês 202008
        $painel['meses'][1]['negociostotais'] = 1;
        // Altero dados esperados do mês 202008, mês de qualificacao 202008
        $painel['meses'][1]['listaqualificacao'][0]['negociosdesqualificados'] = 1;

        /** CLASSIFICADORES ESPERADOS */
        // Campanha de origem - Campanha 01
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_CAMPANHAORIGEM,
            'id' => $campanhaOrigem1['promocaolead'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Campanha de origem - Campanha 02
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_CAMPANHAORIGEM,
            'id' => $campanhaOrigem2['promocaolead'],
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [],
            'negociosdesqualificados' => [],
        ];

        // Campanha de origem - Não preenchido
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_CAMPANHAORIGEM,
            'id' => null,
            'prenegocios' => [],
            'negociosqualificados' => [],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Mídia de origem
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_MIDIAORIGEM,
            'id' => $midia1['midiaorigem'],
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Tipo de Acionamento
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_TIPOACIONAMENTO,
            'id' => $tipoAcionamento1['tiposacionamento'],
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Estabelecimento
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_ESTABELECIMENTO,
            'id' => $estabelecimento,
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];
        
        // Área de negocio
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_AREANEGOCIO,
            'id' => $operacao1['proposta_operacao'],
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];
        
        // Atribuição
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_ATRIBUICAO,
            'id' => $vendedor['vendedor_id'],
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Segmento de atuação
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_SEGMENTOATUACAO,
            'id' => $segmentoatuacao['segmentoatuacao'],
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Faturamento Anual
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_FATURAMENTOANUAL,
            'id' => $faturamentoanual,
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Já é cliente: SIM
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_EHCLIENTE,
            'id' => true,
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Já é cliente: NÃO
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_EHCLIENTE,
            'id' => false,
            'prenegocios' => [],
            'negociosqualificados' => [],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Data de qualificação
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_QUALIFICADOEM,
            'id' => '202008',
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Motivo de desqualificação
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_MOTIVODESQUALIFICACAO,
            'id' => $motivoDesqualificacao['motivodesqualificacaoprenegocio'],
            'prenegocios' => [],
            'negociosqualificados' => [],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Cargo do contato: Sócio/proprietário/ceo
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_CARGO,
            'id' => self::CCN_SOCIO_PROPRIETARIO_CEO,
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Cargo do contato: DIRETOR
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_CARGO,
            'id' => self::CCN_DIRETOR,
            'prenegocios' => [
                $negocio2['documento']
            ],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Data de início do painel
        $dataPainel = new \DateTime('2020-07-15');
        // Data de fim do painel
        $dataPainelFim = new \DateTime('2021-06-07');

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial,
            'datainicio' => $dataPainel->format('Y-m-d'),
            'datafinal' => $dataPainelFim->format('Y-m-d'),
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_get($I, $filtros, $painel);
    }

    /**
     * Testa busca do painel de marketing, filtrando por campanha
     */
    public function getComFiltroCampanha(FunctionalTester $I){
        /* Preparação do cenário */
        // Crio requisitos do negócio
        $campanhaOrigem1 = $I->haveInDatabasePromocaoLead($I);
        $campanhaOrigem2 = $I->haveInDatabasePromocaoLead($I, [
            'codigo' => 2
        ]);
        $situacaoPrenegocio = $I->haveInDatabaseSituacoesprenegocios($I);
        $operacao1 = $I->haveInDatabaseNegocioOperacao($I);
        $midia1 = $I->haveInDatabaseMidia($I);
        $tipoAcionamento1 = $I->haveInDatabaseTipoAcionamento($I);
        $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';
        $vendedor = [
            'vendedor_id' => 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'
        ];
        $segmentoatuacao = $I->haveInDatabaseSegmentoAtuacao($I);
        $faturamentoanual = '30000000';
        $motivoDesqualificacao = $I->haveInDatabaseMotivoDesqualificacao($I);

        // Crio negócios
        // Negócio qualificado
        $negocio1 = $I->haveInDatabaseNegocio($I, [
            'created_at' => '2020-07-30',
            'id_codigodepromocao' => $campanhaOrigem2,
            'situacaoprenegocio' => $situacaoPrenegocio,
            'operacao' => $operacao1,
            'midia' => $midia1,
            'tipoacionamento' => $tipoAcionamento1,
            'estabelecimento' => $estabelecimento,
            'cliente_captador' => $vendedor,
            'cliente_segmentodeatuacao' => $segmentoatuacao,
            'cliente_receitaanual' => $faturamentoanual,
            'ehcliente' => true,
            'tipoqualificacao_pn' => 1, // Qualificado
            'created_at_qualificacao_pn' => '2020-08-01'
        ]);

        // Pré negócio
        $negocio2 = $I->haveInDatabaseNegocio($I, [
            'created_at' => '2020-07-30',
            'id_codigodepromocao' => $campanhaOrigem1,
            'situacaoprenegocio' => $situacaoPrenegocio,
            'operacao' => $operacao1,
            'midia' => $midia1,
            'tipoacionamento' => $tipoAcionamento1,
            'estabelecimento' => $estabelecimento,
            'cliente_captador' => $vendedor,
            'cliente_segmentodeatuacao' => $segmentoatuacao,
            'cliente_receitaanual' => $faturamentoanual,
            'ehcliente' => true,
        ]);

        // Negócio desqualificado
        $negocio3 = $I->haveInDatabaseNegocio($I, [
            'created_at' => '2020-08-23',
            'id_codigodepromocao' => $campanhaOrigem2,
            'situacaoprenegocio' => $situacaoPrenegocio,
            'operacao' => $operacao1,
            'midia' => $midia1,
            'tipoacionamento' => $tipoAcionamento1,
            'estabelecimento' => $estabelecimento,
            'cliente_captador' => $vendedor,
            'cliente_segmentodeatuacao' => $segmentoatuacao,
            'cliente_receitaanual' => $faturamentoanual,
            'ehcliente' => false,
            'tipoqualificacao_pn' => 2, // Desqualificado
            'created_at_qualificacao_pn' => '2020-08-24',
            'id_motivodesqualificacao_pn' => $motivoDesqualificacao['motivodesqualificacaoprenegocio'] // Motivo da desqualificação
        ]);

        // Defino meses de retorno
        $arrMeses = ['202007', '202008'];

        $painel = [
            'meses' => [],
            'classificadores' => []
        ];

        /** DADOS DE MESES ESPERADOS */
        // Monto retorno esperado dos meses do painel
        foreach ($arrMeses as $indexMes => $mes) {
            $painelMes = [
                'anomes' => $mes,
                'negociostotais' => 0,
                'listaqualificacao' => []
            ];

            foreach ($arrMeses as $indexMesQualificacao => $mesQualificacao) {
                if ($indexMesQualificacao >= $indexMes) {
                    $painelMesQualificacao = [
                        'anomes' => $mesQualificacao,
                        'negociosqualificados' => 0,
                        'negociosdesqualificados' => 0,
                    ];
    
                    $painelMes['listaqualificacao'][] = $painelMesQualificacao;
                }
            }

            $painel['meses'][] = $painelMes;
        }

        // Altero dados esperados do mês 202007
        $painel['meses'][0]['negociostotais'] = 1;
        // Altero dados esperados do mês 202007, mês de qualificacao 202008
        $painel['meses'][0]['listaqualificacao'][1]['negociosqualificados'] = 1;

        // Altero dados esperados do mês 202008
        $painel['meses'][1]['negociostotais'] = 1;
        // Altero dados esperados do mês 202008, mês de qualificacao 202008
        $painel['meses'][1]['listaqualificacao'][0]['negociosdesqualificados'] = 1;

        /** CLASSIFICADORES ESPERADOS */
        // Campanha de origem
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_CAMPANHAORIGEM,
            'id' => $campanhaOrigem2['promocaolead'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Mídia de origem
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_MIDIAORIGEM,
            'id' => $midia1['midiaorigem'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Tipo de Acionamento
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_TIPOACIONAMENTO,
            'id' => $tipoAcionamento1['tiposacionamento'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Estabelecimento
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_ESTABELECIMENTO,
            'id' => $estabelecimento,
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Área de negocio
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_AREANEGOCIO,
            'id' => $operacao1['proposta_operacao'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Atribuição
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_ATRIBUICAO,
            'id' => $vendedor['vendedor_id'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Segmento de atuação
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_SEGMENTOATUACAO,
            'id' => $segmentoatuacao['segmentoatuacao'],
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Faturamento Anual
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_FATURAMENTOANUAL,
            'id' => $faturamentoanual,
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Já é cliente: SIM
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_EHCLIENTE,
            'id' => true,
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Já é cliente: NÃO
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_EHCLIENTE,
            'id' => false,
            'prenegocios' => [],
            'negociosqualificados' => [],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Data de qualificação
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_QUALIFICADOEM,
            'id' => '202008',
            'prenegocios' => [],
            'negociosqualificados' => [
                $negocio1['documento']
            ],
            'negociosdesqualificados' => [],
        ];

        // Motivo de desqualificação
        $painel['classificadores'][] = [
            'entidade' => self::PMCE_MOTIVODESQUALIFICACAO,
            'id' => $motivoDesqualificacao['motivodesqualificacaoprenegocio'],
            'prenegocios' => [],
            'negociosqualificados' => [],
            'negociosdesqualificados' => [
                $negocio3['documento']
            ],
        ];

        // Data de início do painel
        $dataPainel = new \DateTime('2020-07-15');
        // Data de fim do painel
        $dataPainelFim = new \DateTime('2020-08-23');

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial,
            'datainicio' => $dataPainel->format('Y-m-d'),
            'datafinal' => $dataPainelFim->format('Y-m-d'),
            'campanha' => $campanhaOrigem2['promocaolead'],
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_get($I, $filtros, $painel);
    }

    /**
     * Testa retorno de erro ao passar uma data de início inválida
     */
    public function getRetornoBadRequestDataInicioInvalida(FunctionalTester $I){
        /* Preparação do cenário */
        $dados = [];

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial,
            'datainicio' => '2020-01-xx',
            'datafinal' => '2020-02-10',
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_get($I, $filtros, $dados, HttpCode::BAD_REQUEST);
    }

    /**
     * Testa retorno de erro ao passar uma data final inválida
     */
    public function getRetornoBadRequestDataFinalInvalida(FunctionalTester $I){
        /* Preparação do cenário */
        $dados = [];

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial,
            'datainicio' => '2020-01-10',
            'datafinal' => '2020-02-xx',
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_get($I, $filtros, $dados, HttpCode::BAD_REQUEST);
    }

    /**
     * Testa retorno de erro ao passar um período com data inicial menor que data final
     */
    public function getRetornoBadRequestDataFinalMenorQueInicial(FunctionalTester $I){
        /* Preparação do cenário */
        $dados = [];

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial,
            'datainicio' => '2020-01-15',
            'datafinal' => '2020-01-10',
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_get($I, $filtros, $dados, HttpCode::BAD_REQUEST);
    }

    /**
     * Testa retorno de erro ao passar um período com mais de 12 meses
     */
    public function getRetornoBadRequestMaisDeUmAno(FunctionalTester $I){
        /* Preparação do cenário */
        $dados = [];

        $filtros = [
            'grupoempresarial' => $I->grupoempresarial,
            'datainicio' => '2020-01-15',
            'datafinal' => '2021-01-10',
        ];

        /* Execução e validação da funcionalidade */
        $retorno = $this->_get($I, $filtros, $dados, HttpCode::BAD_REQUEST);
    }
}