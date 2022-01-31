<?php

namespace Nasajon\AppBundle\Service\Relatorios;

use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\MDABundle\Entity\Ns\Prospects;
use Nasajon\MDABundle\Entity\Crm\Listadavezvendedoresitens;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\AppBundle\DTO\Crm\PainelMarketingDTO;
use LogicException;

class RelatorioPainelMarketingService
{
    /**
     * Repository do painel de marketing
     * @var \Nasajon\AppBundle\Repository\Relatorios\RelatorioPainelMarketingRepository
    */
    protected $repository;

    // Constantes com o tipo de qualificação do negócio
    const TIPO_QUALIFICACAO_SEM_QUALIFICACAO = 0;
    const TIPO_QUALIFICACAO_QUALIFICADO = 1;
    const TIPO_QUALIFICACAO_DESQUALIFICADO = 2;

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
  

    public function __construct(
        \Nasajon\AppBundle\Repository\Relatorios\RelatorioPainelMarketingRepository $repository
    ){  
        $this->repository = $repository;
    }

    /**
     * Retorna o repository do painel de marketing
     * @return \Nasajon\AppBundle\Repository\Relatorios\RelatorioPainelMarketingRepository
     */
    public function getRepository(){
        return $this->repository;
    }

    /**
     * Busca dados utilizados para montar o painel de marketing dentro do período $datainicio e $datafinal.
     * Filtro por campanha é opcional.
     */
    public function getPainelMarketing($tenant, $id_grupoempresarial, $datainicio, $datafinal, $campanha = null){
        try {
            // Busco negócios e contatos do banco de dados
            $arrNegocios = $this->getRepository()->getNegocios($tenant, $id_grupoempresarial, $datainicio, $datafinal, $campanha);
            $arrNegociosContatos = $this->getRepository()->getNegociosContatos($tenant, $id_grupoempresarial, $datainicio, $datafinal, $campanha);
            $arrNegociosIDs = [];

            // Monto estrutura inicial do painel
            $painel = new PainelMarketingDTO(
                new \DateTime($datainicio), 
                new \DateTime($datafinal)
            );

            // Percorro a lista de negócios para contabilizar informações ao painel
            for ($i=0; $i < count($arrNegocios); $i++) { 
                $negocio = $arrNegocios[$i];
                // Guardo ID dos negócios. Isso será utilizado para não contabilizar contatos que não tenham sido retornados
                //  na consulta de negócios
                $arrNegociosIDs[] = $negocio['negocio_id'];
                
                // Contabilizo informações de qualificação e criação por mês ao painel
                $painel->contabilizarNoMes(
                    $negocio['anomes_criacao'], 
                    ($negocio['tipoqualificacao'] == self::TIPO_QUALIFICACAO_SEM_QUALIFICACAO) ? null : $negocio['anomes_qualificacao'],
                    $negocio['tipoqualificacao'] == self::TIPO_QUALIFICACAO_QUALIFICADO
                );

                // Contabilizo classificador de campanha de origem
                $painel->contabilizarClassificador(
                    self::PMCE_CAMPANHAORIGEM, 
                    $negocio['campanha_id'], 
                    $negocio['campanha_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de mídia de origem
                $painel->contabilizarClassificador(
                    self::PMCE_MIDIAORIGEM, 
                    $negocio['midia_id'], 
                    $negocio['midia_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de tipo de acionamento
                $painel->contabilizarClassificador(
                    self::PMCE_TIPOACIONAMENTO, 
                    $negocio['tipoacionamento_id'], 
                    $negocio['tipoacionamento_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de estabelecimento
                $painel->contabilizarClassificador(
                    self::PMCE_ESTABELECIMENTO, 
                    $negocio['estabelecimento_id'], 
                    $negocio['estabelecimento_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de área de negócio
                $painel->contabilizarClassificador(
                    self::PMCE_AREANEGOCIO, 
                    $negocio['area_id'], 
                    $negocio['area_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de atribuição
                $painel->contabilizarClassificador(
                    self::PMCE_ATRIBUICAO, 
                    $negocio['captador_id'], 
                    $negocio['captador_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de segmento de atuação
                $painel->contabilizarClassificador(
                    self::PMCE_SEGMENTOATUACAO, 
                    $negocio['segmentoatuacao_id'], 
                    $negocio['segmentoatuacao_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de faturamento anual
                $painel->contabilizarClassificador(
                    self::PMCE_FATURAMENTOANUAL, 
                    $negocio['faturamento_nome'], 
                    $negocio['faturamento_nome'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                // Contabilizo classificador de É cliente
                $painel->contabilizarClassificador(
                    self::PMCE_EHCLIENTE, 
                    $negocio['ja_e_cliente'], 
                    ($negocio['ja_e_cliente'] == true) ? 'Sim' : 'Não',
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );

                if ($negocio['tipoqualificacao'] == self::TIPO_QUALIFICACAO_QUALIFICADO){
                    // Contabilizo classificador de anomes da qualificação
                    $painel->contabilizarClassificador(
                        self::PMCE_QUALIFICADOEM, 
                        $negocio['anomes_qualificacao'], 
                        $negocio['anomes_qualificacao'],
                        $negocio['negocio_id'],
                        $negocio['tipoqualificacao']
                    );
                } else if ($negocio['tipoqualificacao'] == self::TIPO_QUALIFICACAO_DESQUALIFICADO){
                    // Contabilizo classificador de motivo de desqualificação
                    $painel->contabilizarClassificador(
                        self::PMCE_MOTIVODESQUALIFICACAO, 
                        $negocio['motivo_id'], 
                        $negocio['motivo_nome'],
                        $negocio['negocio_id'],
                        $negocio['tipoqualificacao']
                    );
                }
            }

            // Percorro os contatos dos negócios para contabilizar classificadores de cargos de contato
            while($negocioContato = array_pop($arrNegociosContatos)) {
                $negocioIndex = array_search($negocioContato['negocio_id'], $arrNegociosIDs);
                // Se não encontrou o negócio na lista, passo para o próximo contato
                if ($negocioIndex <= -1) {
                    continue;
                }
                // Pego negócio da lista de negócios
                $negocio = $arrNegocios[$negocioIndex];

                // Contabilizo classificador de cargo de contato
                $painel->contabilizarClassificador(
                    self::PMCE_CARGO, 
                    $negocioContato['contato_cargo'], 
                    $negocioContato['contato_cargo'],
                    $negocio['negocio_id'],
                    $negocio['tipoqualificacao']
                );
            }

            // Retorno dados
            return $painel;
            
        }  catch (\Exception $e) {
            throw $e;
        }
    }
}