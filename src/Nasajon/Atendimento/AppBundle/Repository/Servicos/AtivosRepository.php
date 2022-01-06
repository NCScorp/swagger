<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Servicos;

use Nasajon\MDABundle\Repository\Servicos\AtivosRepository as ParentRepository;
use Nasajon\MDABundle\Request\Filter;

class AtivosRepository extends ParentRepository {

    public function findAll($tenant, $cliente, Filter $filter = null) {
        $ativos = parent::findAll($tenant, $cliente, $filter);
        $retorno = [];
        $ativosIndexacao = [];

        foreach ($ativos as $ativo) {

            if (!isset($ativosIndexacao[$ativo['servico_id']])) {
                $ativosIndexacao[$ativo['servico_id']] = count($retorno);
                $retorno[$ativosIndexacao[$ativo['servico_id']]] = [
                    'servico_id' => $ativo['servico_id'],
                    'servico_codigo' => $ativo['servico_codigo'],
                    'servico_descricao' => $ativo['servico_descricao'],
                    'contrato_id' => $ativo['contrato_id'],
                    'contrato_codigo' => $ativo['contrato_codigo'],
                    'ofertas' => []
                ];
            }


            if (!isset($ofertasIndexacao[$ativo['oferta_id']])) {

                $ofertasIndexacao[$ativo['oferta_id']] = count($retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas']);

                if ($ativo['oferta_pacote']) {
                    $retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas'][$ofertasIndexacao[$ativo['oferta_id']]] = [
                            'oferta_id' => $ativo['oferta_id'],
                            'oferta_ativa' => $ativo['oferta_ativa'],
                            'oferta_codigo' => $ativo['oferta_codigo'],
                            'oferta_descricao' => $ativo['oferta_descricao'],
                            'oferta_servicocatalogo' => $ativo['oferta_servicocatalogo'],
                            'ofertas' => [],
                            'componentes' => []
                        ];
                } else {

                    if (!empty($ativo['oferta_pacote_pai'])) {

                        $ofertasPaiIndexacao[$ativo['oferta_pacote_pai']] = count($retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas'][$ofertasIndexacao[$ativo['oferta_pacote_pai']]]['ofertas']);

                        $retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas'][$ofertasIndexacao[$ativo['oferta_pacote_pai']]]['ofertas'][$ofertasPaiIndexacao[$ativo['oferta_pacote_pai']]] = [
                            'oferta_id' => $ativo['oferta_id'],
                            'oferta_ativa' => $ativo['oferta_ativa'],
                            'oferta_codigo' => $ativo['oferta_codigo'],
                            'oferta_descricao' => $ativo['oferta_descricao'],
                            'oferta_servicocatalogo' => $ativo['oferta_servicocatalogo'],
                            'componentes' => []
                        ];
                    } else {
                        $retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas'][$ofertasIndexacao[$ativo['oferta_id']]] = [
                            'oferta_id' => $ativo['oferta_id'],
                            'oferta_ativa' => $ativo['oferta_ativa'],
                            'oferta_codigo' => $ativo['oferta_codigo'],
                            'oferta_descricao' => $ativo['oferta_descricao'],
                            'oferta_servicocatalogo' => $ativo['oferta_servicocatalogo'],
                            'componentes' => []
                        ];
                    }
                }
            }

            if (!empty($ativo['componente_id'])) {
                    if (!empty($ativo['oferta_pacote_pai'])) {

                        $retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas'][$ofertasIndexacao[$ativo['oferta_pacote_pai']]]['ofertas'][$ofertasPaiIndexacao[$ativo['oferta_pacote_pai']]]['componentes'][] = [
                            'componente_id' => $ativo['componente_id'],
                            'componente_codigo' => $ativo['componente_codigo'],
                            'componente_descricao' => $ativo['componente_descricao'],
                            'componente_quantidade' => (int)$ativo['componente_quantidade'],
                            'componente_ativa' => (int)$ativo['componente_ativa'],
                        ];

                    } else {

                        $retorno[$ativosIndexacao[$ativo['servico_id']]]['ofertas'][$ofertasIndexacao[$ativo['oferta_id']]]['componentes'][] = [
                            'componente_id' => $ativo['componente_id'],
                            'componente_codigo' => $ativo['componente_codigo'],
                            'componente_descricao' => $ativo['componente_descricao'],
                            'componente_quantidade' => (int)$ativo['componente_quantidade'],
                            'componente_ativa' => (int)$ativo['componente_ativa'],
                        ];
                    }
                }
        }

        return $retorno;
    }



}
