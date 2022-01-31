<?php

namespace Nasajon\AppBundle\DTO\Crm;

use Nasajon\AppBundle\DTO\Crm\PainelMarketingMesDTO;
use Nasajon\AppBundle\DTO\Crm\PainelMarketingMesQualificacaoDTO;
use Nasajon\AppBundle\DTO\Crm\PainelMarketingClassificadorDTO;

/**
 * Classe referente ao dados do painel de marketing
 */
class PainelMarketingDTO {
    public $meses = [];
    public $classificadores = [];

    // Constantes com o tipo de qualificação do negócio
    const TIPO_QUALIFICACAO_SEM_QUALIFICACAO = 0;
    const TIPO_QUALIFICACAO_QUALIFICADO = 1;
    const TIPO_QUALIFICACAO_DESQUALIFICADO = 2;

    public function __construct(
        $datainicio,
        $datafim
    ){
        // Pego período de meses que serão retornados no painel
        $intervalo = $datainicio->diff($datafim);
        
        $intervaloMeses = ($intervalo->y * 12) + $intervalo->m + 1;

        if ( ((int)$datainicio->format('d')) > ((int)$datafim->format('d')) ) {
            $intervaloMeses++;
        }

        // Crio array de labels dos meses a serem retornados no painel
        $arrMesesLabel = [];

        for ($i=0; $i < $intervaloMeses; $i++) {
            $arrMesesLabel[] = $datainicio->format('Ym');

            $datainicio->add(new \DateInterval('P1M'));
        }

        // Inicializo informações de meses do painel
        foreach ($arrMesesLabel as $indexMes => $mesLabel) {
            $painelMarketingMes = new PainelMarketingMesDTO($mesLabel);
            
            foreach ($arrMesesLabel as $indexMesQualificacao => $mesLabelQualificacao) {
                // Adiciono meses de qualificação maiores ou iguais ao mês de criação
                if ($indexMesQualificacao >= $indexMes) {
                    $painelMarketingMesQualificacao = new PainelMarketingMesQualificacaoDTO($mesLabelQualificacao);
                    $painelMarketingMes->listaqualificacao[] = $painelMarketingMesQualificacao;
                }
            }

            $this->meses[] = $painelMarketingMes;
        }
    }

    /**
     * Contabiliza 1 aos negociostotais do $anoMes passado.
     * Contabiliza uma qualificação no $anoMesQualificacao passado, referênte ao $anoMes.
     */
    public function contabilizarNoMes($anoMes, $anoMesQualificacao, $qualificado){
        for ($i=0; $i < count($this->meses); $i++) { 
            // Se encontrar o mês informado, somo 1 aos negócios totais e contabilizo a qualificação
            if ($this->meses[$i]->anomes == $anoMes) {
                $this->meses[$i]->negociostotais++;

                // Só contabilizo qualificação se o anomes não estiver nulo
                if ($anoMesQualificacao != null) {
                    $this->meses[$i]->contabilizarQualificacao($anoMesQualificacao, $qualificado);
                }
                
                break;
            }
        }
    }

    /**
     * Contabiliza negócio no classificador
     */
    public function contabilizarClassificador($classificador_entidade, $classificador_id, $classificador_nome, $negocio_id, $negocio_qualificacao){
        $classificadorIndex = -1;
        $painelMarketingClassificador = null;

        // Busco o classificador, caso já esteja cadastrado na lista
        for ($i=0; $i < count($this->classificadores); $i++) { 
            $classificadorDaLista = $this->classificadores[$i];
            
            // Se encontrou o classificador, saio do loop
            if ($classificadorDaLista->entidade == $classificador_entidade && $classificadorDaLista->id == $classificador_id) {
                $painelMarketingClassificador = $classificadorDaLista;
                $classificadorIndex = $i;
                break;
            }
        }

        // Se não encontrou o classificador na lista, crio um
        if ($classificadorIndex == -1) {
            $painelMarketingClassificador = new PainelMarketingClassificadorDTO($classificador_entidade, $classificador_id, $classificador_nome);
        }

        // Adiciono negócio ao classificador, de acordo com o tipo de qualificação
        switch ($negocio_qualificacao) {
            case self::TIPO_QUALIFICACAO_SEM_QUALIFICACAO: {
                $painelMarketingClassificador->prenegocios[] = $negocio_id;
                break;
            }
            case self::TIPO_QUALIFICACAO_QUALIFICADO: {
                $painelMarketingClassificador->negociosqualificados[] = $negocio_id;
                break;
            }
            case self::TIPO_QUALIFICACAO_DESQUALIFICADO: {
                $painelMarketingClassificador->negociosdesqualificados[] = $negocio_id;
                break;
            }
            
            default: break;
        }

        // Se o classificador já existia na lista, atualizo.
        if ($classificadorIndex > -1) {
            $this->classificadores[$classificadorIndex] = $painelMarketingClassificador;
        } 
        // Se não existia, adiciono
        else {
            $this->classificadores[] = $painelMarketingClassificador;
        }
    }
}