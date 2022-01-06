<?php

namespace Nasajon\Atendimento\AppBundle\Event\Processor;

use Nasajon\Atendimento\AppBundle\Event\Processor\FollowupProcessor;

class FollowupProcessorTest extends \Codeception\Test\Unit {

    private $service;
    private $followups;
    private $obsRepo;
    private $tenantRepo;
    private $atendimentos;
    private $equipeRepository;
    private $equipeFilter;

    protected function setUp() {
        $this->followups = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\FollowupsRepository')->disableOriginalConstructor()->getMock();
        $this->atendimentos = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\SolicitacoesRepository')->disableOriginalConstructor()->getMock();
        $this->obsRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosobservadoresRepository')->disableOriginalConstructor()->getMock();
        $this->tenantRepo = $this->getMockBuilder('Nasajon\ModelBundle\Repository\Ns\TenantsRepository')->disableOriginalConstructor()->getMock();
        $this->equipeRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository')->disableOriginalConstructor()->getMock();
        $this->confService = $this->getMockBuilder('Nasajon\ModelBundle\Services\ConfiguracoesService')->disableOriginalConstructor()->getMock();
        $this->equipeFilter = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService')->disableOriginalConstructor()->getMock();
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')->disableOriginalConstructor()->getMock();
        $this->usuarioDisponibilidade = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\UsuariosDisponibilidadesRepository')->disableOriginalConstructor()->getMock();
        $this->usuarioDisponibilidadeService = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Service\UsuariosDisponibilidadesService')->disableOriginalConstructor()->getMock();

        $em->method('getRepository')->willReturn($this->tenantRepo);

        $this->service = new FollowupProcessor($this->followups, $this->atendimentos, $this->obsRepo, null,  $this->equipeRepository, $this->confService, $this->usuarioDisponibilidadeService, $this->equipeFilter, 'dev.nasajonsistemas.com.br', $em, $router);
    }

    private function getEvent($type) {
        $event = new \Nasajon\Atendimento\AppBundle\Event\Event();
        $event->setType($type);
        $event->setSubject("");
        return $event;
    }

    /**
     * @dataProvider getSubscribers
     */
    public function testGetSubscribers($atendimento, $createdBy, $subscribers, $match) {

        $this->obsRepo->method('findAllWithEquipe')->willReturn($subscribers);
        $sub = $this->service->getSubscribers($atendimento, $createdBy);
        $this->assertEquals($sub, $match);
    }

    public function getSubscribers() {
        $atendimento = [ 'tenant' => 0, 'atendimento' => 'guid', 'cliente'=>null];
        return array(
            array(array_merge($atendimento, ['responsavel_web_tipo' => 1, 'responsavel_web' => 'criador@example.com']), "criador@example.com", [], []),
            array(array_merge($atendimento, ['responsavel_web_tipo' => 0, 'responsavel_web' => '']), "criador@example.com", [['usuario' => 'inscrito1@example.com', "todosclientes" => 1]], ['inscrito1@example.com']),
            array(array_merge($atendimento, ['responsavel_web_tipo' => 1, 'responsavel_web' => 'responsavel@example.com']), "criador@example.com", [['usuario' => 'inscrito1@example.com', "todosclientes" => 1]], ['inscrito1@example.com', 'responsavel@example.com']),
            array(array_merge($atendimento, ['responsavel_web_tipo' => 1, 'responsavel_web' => 'responsavel@example.com']), "criador@example.com", [['usuario' => 'inscrito1@example.com', "todosclientes" => 1], ['usuario' => 'inscrito1@example.com', "todosclientes" => 1]], ['inscrito1@example.com', 'responsavel@example.com']),
            array(array_merge($atendimento, ['responsavel_web_tipo' => 1, 'responsavel_web' => 'responsavel@example.com']), "criador@example.com", [['usuario' => 'inscrito1@example.com', "todosclientes" => 1], ['usuario' => 'responsavel@example.com', "todosclientes" => 1]], ['inscrito1@example.com', 'responsavel@example.com']),
            array(array_merge($atendimento, ['responsavel_web_tipo' => 1, 'responsavel_web' => 'responsavel@example.com']), "responsavel@example.com", [['usuario' => 'inscrito1@example.com', "todosclientes" => 1], ['usuario' => 'responsavel@example.com', "todosclientes" => 1]], ['inscrito1@example.com']),
            array(array_merge($atendimento, ['responsavel_web_tipo' => 1, 'responsavel_web' => 'responsavel@example.com']), 'inscrito1@example.com', [['usuario' => 'inscrito1@example.com', "todosclientes" => 1], ['usuario' => 'responsavel@example.com', "todosclientes" => 1]], ['responsavel@example.com']),
        );
    }

    /**
     *
     */
    public function testRespostaCriadaPeloClienteSemSubscribers() {
        $this->followups->method('find')->willReturn([
            "followup" => 'guid',
            "atendimento" => 'guid',
            "created_at" => (new \DateTime)->format(\DateTime::W3C),
            "tipo" => 0,
            "created_by" => [
                'nome' => 'Jon Doe',
                'email' => 'cliente@example.com'
            ],
            "criador" => true,
            "tenant" => 0
        ]);
        $this->atendimentos->method('find')->willReturn([
            "followups" => [],
            "tenant" => 0,
            "created_at" => (new \DateTime)->format(\DateTime::W3C),
            'email' => 'cliente@example.com',
            'atendimento' => 'guid',
            'responsavel_web_tipo' => 1,
            'responsavel_web' => 'responsavel@example.com',
            'numeroprotocolo' => '123456',
            'resumo' => 'Resumo',
            "cliente" => null
        ]);
        $this->tenantRepo->method('find')->willReturn(new \Nasajon\ModelBundle\Entity\Ns\Tenants());
        $this->obsRepo->method('findAllWithEquipe')->willReturn([]);
        $notification = $this->service->process($this->getEvent(\Nasajon\Atendimento\AppBundle\Event\Event::FOLLOWUP_CREATE_TYPE));

        $this->assertEquals(1, count($notification));
        $this->assertEquals(FollowupProcessor::TEMPLATE_PARA_ADMIN, $notification[0]->getTemplate());
        $this->assertEquals(['responsavel@example.com'], $notification[0]->getUser());
    }

    public function testRespostaCriadaPeloResponsavelSemSubscribers() {
        $this->followups->method('find')->willReturn([
            "followup" => 'guid',
            "atendimento" => 'guid',
            "created_at" => (new \DateTime)->format(\DateTime::W3C),
            "tipo" => 0,
            "created_by" => [
                'nome' => 'Jon Doe',
                'email' => 'responsavel@example.com'
            ],
            "criador" => false,
            "tenant" => 0
        ]);
        $this->atendimentos->method('find')->willReturn([
            "followups" => [],
            "tenant" => 0,
            "created_at" => (new \DateTime)->format(\DateTime::W3C),
            'email' => 'cliente@example.com',
            'atendimento' => 'guid',
            'responsavel_web_tipo' => 1,
            'responsavel_web' => 'responsavel@example.com',
            'numeroprotocolo' => '123456',
            'resumo' => 'Resumo',
            'visivelparacliente' => true,
            'cliente' => null
        ]);
        $this->tenantRepo->method('find')->willReturn(new \Nasajon\ModelBundle\Entity\Ns\Tenants());
        $this->obsRepo->method('findAllWithEquipe')->willReturn([]);
        $notification = $this->service->process($this->getEvent(\Nasajon\Atendimento\AppBundle\Event\Event::FOLLOWUP_CREATE_TYPE));

        $this->assertEquals(1, count($notification));
        $this->assertEquals(FollowupProcessor::TEMPLATE_PARA_CLIENTE, $notification[0]->getTemplate());
        $this->assertEquals(['cliente@example.com'], $notification[0]->getUser());
    }

}
