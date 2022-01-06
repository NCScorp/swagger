<?php

namespace Nasajon\Atendimento\AppBundle\Notification;

use Nasajon\Atendimento\AppBundle\Notification\NotificationDispatcher;
use Nasajon\Atendimento\AppBundle\Notification\Notification;

class NotificationDispatcherTest extends \Codeception\Test\Unit {
  
  private $service;
  private $maladireta;

  protected function setUp() {
      $this->maladireta = $this->getMockBuilder('Nasajon\SDK\MalaDireta\MalaDiretaClient')->disableOriginalConstructor()->getMock();
      $this->service = new NotificationDispatcher($this->maladireta);
  }

  public function validateUsers() {
    return [
        ["financas@nasajon.com.br","financas@nasajon.com.br"],
        ["atendimentoweb@nasajon.com.br", "atendimentoweb@nasajon.com.br"],
        ["a9c8440e-c605-465c-848a-d5da85f150b0",NULL],
        [
            ["financas@nasajon.com.br", "atendimentoweb@nasajon.com.br", "administrativo@nasajon.com.br"],
            ["financas@nasajon.com.br", "atendimentoweb@nasajon.com.br", "administrativo@nasajon.com.br"]
        ],
        [
            ["financas@nasajon.com.br", "atendimentoweb@nasajon.com.br", "a9c8440e-c605-465c-848a-d5da85f150b0"],
            ["financas@nasajon.com.br", "atendimentoweb@nasajon.com.br"]
        ],
        [
            ["a9c8440e-c605-465c-848a-d5da85f150b0", "86ae5751-4e52-4083-bfee-4486ef75b32a"],
            []
        ]
    ];
  }
    
  /**
   * @dataProvider validateUsers
   */
  public function testValidateUsers($users, $match) {
    $validatedUsers = $this->service->validateUsers($users);
    $this->assertEquals($match, $validatedUsers);
  }
  
  public function testDispatchEncaminhaUmaNotificacao() {
    $n = new Notification();
    
    $mock = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Notification\NotificationDispatcher')
            ->setMethods(['validateUsers', '_realDispatch'])
            ->disableOriginalConstructor()
            ->getMock();
    
    $mock->expects($this->once())
            ->method('validateUsers')
            ->will($this->returnCallback(function() {
                return 'atendimento@nasajon.com.br';
          }));
          
    $mock->expects($this->once())
            ->method('_realDispatch');
          
    $mock->dispatch($n);
  }
  
  public function testDispatchEncaminhaArrayNotificacoes() {
    $n = [new Notification(), new Notification()];
    
    $mock = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Notification\NotificationDispatcher')
            ->setMethods(['validateUsers', '_realDispatch'])
            ->disableOriginalConstructor()
            ->getMock();
    
    $mock->expects($this->exactly(2))
            ->method('validateUsers')
            ->will($this->returnCallback(function() {
                return 'atendimento@nasajon.com.br';
          }));
          
    $mock->expects($this->exactly(2))
            ->method('_realDispatch');
          
    $mock->dispatch($n);
  }
}
