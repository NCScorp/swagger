<?php

namespace Nasajon\AppBundle\Service\Common;

use Nasajon\SDK\Diretorio\DiretorioClient;

class EnvioEmailService
{
    public function __construct(
        \Nasajon\SDK\Diretorio\DiretorioClient $diretorio
    ){
        $this->diretorio = $diretorio;
    }

    /**
     * Dispara requisição para o sistema Diretorio para enviar o E-mail
     */
    public function enviarEmail($dados = [], $tenant)
    {
        $retorno = $this->diretorio->enviaEmail([
            'to' => $dados['to'],
            'from' => 'noreply@nasajon.com.br', // $dados['from'], Atualmente, o remetente deve ser fixo, e não tem suporte pra e-mail de resposta.
            // 'split' => isset($dados['split']) ? $dados['split'] : true, Campo causava erro ao enviar mais de um anexo, só enviava um
            'codigo' => $dados['codigo'],
            'tags' => isset($dados['tags']) ? $dados['tags'] : [],
            'tenant' => $tenant,
            'attachments' => isset($dados['attachments']) ? $dados['attachments'] : [],
            'attachments_names' => isset($dados['attachments_names']) ? $dados['attachments_names'] : [],
            'attachments_content_types' => isset($dados['attachments_content_types']) ? $dados['attachments_content_types'] : [],
        ]);
        return $retorno;
    }
}
