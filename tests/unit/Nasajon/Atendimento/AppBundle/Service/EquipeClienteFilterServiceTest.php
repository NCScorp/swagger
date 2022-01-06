<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Service\EquipeClienteFilterService;
use Nasajon\Atendimento\AppBundle\Builder\SQLExpressionBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

class EquipeClienteFilterServiceTest extends \Codeception\Test\Unit {

    private $service;

    protected function setUp() {
        $equipeRepo= $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\EquipesRepository')->disableOriginalConstructor()->getMock();
        $fixedAttrbRepo = $this->getMockBuilder('Symfony\Component\DependencyInjection\ParameterBag\ParameterBag')->disableOriginalConstructor()->getMock();
        $provisaoRepo = $this->getMockBuilder('Nasajon\LoginBundle\Entity\Provisao')->disableOriginalConstructor()->getMock();

        $this->service = new EquipeClienteFilterService($equipeRepo, $fixedAttrbRepo, $provisaoRepo);
    }
    
    public function filtroEquipes() {
      return array(
        array(
            [
                [
                  'in' => [],
                  'ex' => [['equipeclienteregra' => '5bb26d80-707e-4a09-8dd0-9228c043d8d4', 'campo' => 'bloqueado', 'tipo' => 1, 'operador' => 'is_equal', 'valor' => 1]]
                ]
            ],
            new CompositeExpression('AND', [new CompositeExpression('AND', ['t0_.bloqueado = true'])])
        ),
        array(
            [
                [
                  'in' => [],
                  'ex' => [['equipeclienteregra' => '5bb26d80-707e-4a09-8dd0-9228c043d8d4', 'campo' => 'bloqueado', 'tipo' => 1, 'operador' => 'is_equal', 'valor' => 0]]
                ]
            ],
            new CompositeExpression('AND', [new CompositeExpression('AND', ['t0_.bloqueado = false'])])
        ),
        array(
            [
                [
                  'in' => [],
                  'ex' => [['equipeclienteregra' => '5bb26d80-707e-4a09-8dd0-9228c043d8d4', 'campo' => 'bloqueado', 'tipo' => 1, 'operador' => 'is_equal', 'valor' => 0]]
                ],
                [
                  'in' => [],
                  'ex' => [['equipeclienteregra' => '5bb26d80-707e-4a09-8dd0-9228c043d8d4', 'campo' => 'bloqueado', 'tipo' => 1, 'operador' => 'is_equal', 'valor' => 1]]
                ]
            ],
            new CompositeExpression('AND', [new CompositeExpression('AND', ['t0_.bloqueado = false']), new CompositeExpression('AND', ['t0_.bloqueado = true'])])
        ),
        array(
            [
                [
                  'in' => [['equipeclienteregra' => '5bb26d80-707e-4a09-8dd0-9228c043d8d4', 'campo' => 'bloqueado', 'tipo' => 1, 'operador' => 'is_equal', 'valor' => 0]],
                  'ex' => []
                ],
                [
                  'in' => [],
                  'ex' => [['equipeclienteregra' => '5bb26d80-707e-4a09-8dd0-9228c043d8d4', 'campo' => 'bloqueado', 'tipo' => 1, 'operador' => 'is_equal', 'valor' => 1]]
                ]
            ],
            new CompositeExpression('AND', [new CompositeExpression('OR', ['t0_.bloqueado = false']), new CompositeExpression('AND', ['t0_.bloqueado = true'])])
        )
      );
    }
    
    /**
     * @dataProvider filtroEquipes
     */
    public function testFiltroEquipes($infoEquipe, $match) {
      $builder = new SQLExpressionBuilder();
      $filtro = $builder->buildList($infoEquipe, 't0_');
      $this->assertEquals($match, $filtro);
    }

}
