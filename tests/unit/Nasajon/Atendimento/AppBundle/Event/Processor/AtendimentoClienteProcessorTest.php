<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use Nasajon\Atendimento\AppBundle\Event\Event;
use Nasajon\Atendimento\AppBundle\Event\Processor\AtendimentoClienteProcessor;

class AtendimentoClienteProcessorTest extends \Codeception\Test\Unit {

    private $service;
    private $atendimentosRepo;
    private $observadoresfilasRepo;
    private $tenantRepo;
    private $equipeRepository;
    private $equipeFilter;
    private $usuariodisponibilidadeRepository;

    protected function setUp() {
        $this->atendimentosRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository')->disableOriginalConstructor()->getMock();
        $this->observadoresfilasRepo = $this->getMockBuilder('Nasajon\MDABundle\Repository\Servicos\AtendimentosfilasobservadoresRepository')->disableOriginalConstructor()->getMock();
        $this->usuariodisponibilidadeRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\UsuariosDisponibilidadesRepository')->disableOriginalConstructor()->getMock();
        $this->clienteRepository = null;
        $this->equipeRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository')->disableOriginalConstructor()->getMock();
        $this->equipeFilter = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService')->disableOriginalConstructor()->getMock();
        $confService = $this->getMockBuilder('Nasajon\ModelBundle\Services\ConfiguracoesService')->disableOriginalConstructor()->getMock();
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->method('getRepository')->willReturn($this->tenantRepo);
        $router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')->disableOriginalConstructor()->getMock();


        $this->tenantRepo = $this->getMockBuilder('Nasajon\ModelBundle\Repository\Ns\TenantsRepository')->disableOriginalConstructor()->getMock();

//        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();



        $this->service = new AtendimentoClienteProcessor($this->atendimentosRepo, $this->observadoresfilasRepo, $this->usuariodisponibilidadeRepository , null, $this->clienteRepository ,$this->equipeRepository, $this->equipeFilter, $confService, 'dev.nasajonsistemas.com.br', $em, $router);
    }

    private function getEvent($args) {

        $event = new Event();
        $event->setType(Event::ATENDIMENTO_CLIENTE_CREATE_TYPE);
        $event->setSubject($args);
        return $event;
    }

    public function testAtendimentoSemAutoReplyESemFila() {

        $this->atendimentosRepo->method('find')->willReturn([
            "followups" => [],
            "tenant" => 0,
            'email' => 'cliente@example.com',
            'atendimento' => 'guid',
            'responsavel_web_tipo' => 1,
            'responsavel_web' => 'responsavel@example.com',
            'numeroprotocolo' => '123456',
            'resumo' => 'Resumo'
        ]);

        $notification = $this->service->process($this->getEvent([]));

        $this->assertEquals(0, count($notification));
    }

}
