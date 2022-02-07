<?php 

namespace AppBundle\Resources;

abstract class Permissoes{
    const lista = [
        'meusdados_criacao_sol_falta',
        'meusdados_criacao_sol_alteracao_dados_cadastrais',
        'meusdados_criacao_sol_alteracao_vt'
    ];

    const SOLICITACAO_FALTA_CREATE = 'meusdados_criacao_sol_falta';
    const SOLICITACAO_ALTERACAO_VT_CREATE = 'meusdados_criacao_sol_alteracao_vt';
    const SOLICITACAO_ALTERACAO_ENDERECO_CREATE = 'meusdados_criacao_sol_alteracao_dados_cadastrais';
}