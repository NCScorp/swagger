<?php

use \Codeception\Stub;
use Codeception\Test\Unit;
use Nasajon\Atendimento\AppBundle\Consumer\ChamadosSlasViolacaoConsumer;
use Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository;
use PhpAmqpLib\Message\AMQPMessage;

class ChamadosSlasViolacaoConsumerTest extends Unit
{
    protected function setUp()
    {
        $solicitacoesRepository = $this->getSolicitacoesRepository();
        $horautilservice = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Service\HoraUtilService')->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->disableOriginalConstructor()->getMock();

        $this->slasConsumer = new ChamadosSlasViolacaoConsumer($solicitacoesRepository, $horautilservice, $logger);
    }

    private function getSolicitacoesRepository()
    {
        return Stub::make(SolicitacoesRepository::class, [
            'atendimentoAlterarViolacaoSla' => function ($tenant, $atendimento) {
                return true;
            },
            'findQuery' => function($id, $tenant) {
                return [
                    'atendimento' => 'e96bfdb8-d498-43f2-a5b2-857538c2de05',
                    'data_ultima_resposta' => date('Y-m-d H:i', time()),
                    'created_at' => date('Y-m-d H:i', time()),
                    'qtd_respostas' => 0,
                    'sla' => null,
                    'tempo_primeiro_ativo' => '08:00',
                    'tempo_primeiro' => '08:00',
                    'tempo_proximo_ativo' => '08:00',
                    'tempo_proximo' => '08:00',
                    'tempo_resolucao_ativo' => '08:00',
                    'tempo_resolucao' => '08:00',
                ];
            }
        ]);
    }

    public function testConsumirMensagem()
    {
        $msg = new AMQPMessage(json_encode([
            'tenant' => '47',
            'atendimento' => 'aaaaaaaa-6270-4ec8-a722-8136b6784a9f',
            'buscar' => true,
            'sla' => null,
        ]), []);

        $this->slasConsumer->execute($msg);
    }
}