<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Service\HoraUtilService;

class HoraUtilServiceTest extends \Codeception\Test\Unit{
  
  /**
  * @var \UnitTester
  */
  protected $tester;
  
  private $service;
  
  private $horariosatendimentoRepo;
  private $feriadosRepo;
  private $confService;
  
  protected function _before() {
    $this->horariosatendimentoRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\HorariosatendimentoRepository')->disableOriginalConstructor()->getMock();
    $this->feriadosRepo = $this->getMockBuilder('Nasajon\MDABundle\Repository\Atendimento\FeriadosRepository')->disableOriginalConstructor()->getMock();
    $this->confService = $this->getMockBuilder('Nasajon\ModelBundle\Services\ConfiguracoesService')->disableOriginalConstructor()->getMock();
  }
  
  public function testHoraUtilAntesDoExpediente(){
    
    $horautilservice = new HoraUtilService($this->horariosatendimentoRepo, $this->feriadosRepo, $this->confService);
    $horautil = $horautilservice->proximaHoraUtil(47, new \DateTime('2018-10-25 07:00'), 3600);
    $this->assertTrue( $horautil->format('Y-m-d H:i') == '2018-10-25 08:00' );
  }
  
}