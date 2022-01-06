<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;
use Nasajon\Atendimento\AppBundle\Repository\Cliente\SolicitacoesRepository;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Solicitacoes;

class SolicitacoesRepositoryTest extends \Codeception\Test\Unit {
  
  private $repository;
  private $connection;
  private $uploadService;
  private $followupsRepository;
  private $eventDispatcher;
  private $regrasService;
  private $confService;
  private $camposCustomizadosRepository;
  
  protected function setUp() {
    $this->connection = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
    $this->uploadService = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Service\UploadService')->disableOriginalConstructor()->getMock();
    $this->followupsRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Cliente\FollowupsRepository')->disableOriginalConstructor()->getMock();
    $this->confService = $this->getMockBuilder('Nasajon\ModelBundle\Services\ConfiguracoesService')->disableOriginalConstructor()->getMock();
    $this->camposCustomizadosRepository = $this->getMockBuilder('Nasajon\MDABundle\Repository\Servicos\AtendimentoscamposcustomizadosRepository')->disableOriginalConstructor()->getMock();
    $this->chamadosRegrasProducer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')->disableOriginalConstructor()->getMock();
    $this->eventDispatcher = null;
     
    $this->repository = new SolicitacoesRepository($this->connection, $this->uploadService, $this->followupsRepository, $this->confService, $this->camposCustomizadosRepository, $this->chamadosRegrasProducer, $this->eventDispatcher);
  }
  
  public function testPreenchimentoCamposCustomizadosComCamposCamposCustomizadosDaEntidadeNulo() {
    $this->camposCustomizadosRepository->method('findAll')->willReturn([
        ['atendimentocampocustomizado' => '7da94a77-8504-4bd3-80c8-ac7ded429473'],
        ['atendimentocampocustomizado' => 'ff79d4c7-ec04-47c3-99aa-125544dbbf36'],
        ['atendimentocampocustomizado' => '74517ccb-5902-4ab0-8415-5ec56af3de8a'],
        ['atendimentocampocustomizado' => 'ba85e562-de3f-4c3e-bad6-b23f9b87427a'],
        ['atendimentocampocustomizado' => 'f23bef76-1908-4d8f-ab9a-da67387661e0'],
        ['atendimentocampocustomizado' => '21bc7b26-09e2-4f7f-879d-a9ac4d42eb71']
    ]);
    
    $entity = new Solicitacoes();    
    $entity->setCamposcustomizados(null);
    
    $resultado = $this->repository->preencheCamposCustomizados(47, $entity);
    
    $this->assertEquals([
        '7da94a77-8504-4bd3-80c8-ac7ded429473' => null,
        'ff79d4c7-ec04-47c3-99aa-125544dbbf36' => null,
        '74517ccb-5902-4ab0-8415-5ec56af3de8a' => null,
        'ba85e562-de3f-4c3e-bad6-b23f9b87427a' => null,
        'f23bef76-1908-4d8f-ab9a-da67387661e0' => null,
        '21bc7b26-09e2-4f7f-879d-a9ac4d42eb71' => null
    ], $resultado);
  }
  
  public function testPreenchimentoCamposCustomizadosComCamposCamposCustomizadosPreenchidos() {
    $this->camposCustomizadosRepository->method('findAll')->willReturn([
        ['atendimentocampocustomizado' => '7da94a77-8504-4bd3-80c8-ac7ded429473'],
        ['atendimentocampocustomizado' => 'ff79d4c7-ec04-47c3-99aa-125544dbbf36'],
        ['atendimentocampocustomizado' => '74517ccb-5902-4ab0-8415-5ec56af3de8a'],
        ['atendimentocampocustomizado' => 'ba85e562-de3f-4c3e-bad6-b23f9b87427a'],
    ]);
    
    $entity = new Solicitacoes();    
    $entity->setCamposcustomizados([
        '7da94a77-8504-4bd3-80c8-ac7ded429473' => 'Valor 1'
    ]);
    
    $resultado = $this->repository->preencheCamposCustomizados(47, $entity);
    
    $this->assertEquals([
        '7da94a77-8504-4bd3-80c8-ac7ded429473' => 'Valor 1',
        'ff79d4c7-ec04-47c3-99aa-125544dbbf36' => null,
        '74517ccb-5902-4ab0-8415-5ec56af3de8a' => null,
        'ba85e562-de3f-4c3e-bad6-b23f9b87427a' => null,
    ], $resultado);
  }
  
}
