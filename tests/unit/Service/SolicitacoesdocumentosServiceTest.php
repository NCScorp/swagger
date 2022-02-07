<?php

namespace AppBundle\Service;

use Nasajon\MDABundle\Entity\Meurh\Solicitacoesdocumentos;
use AppBundle\Service\Meurh\SolicitacoesdocumentosService;
use Nasajon\MDABundle\Repository\Meurh\SolicitacoesdocumentosRepository;
use Nasajon\MDABundle\Service\Meurh\SolicitacoesService;
use AppBundle\Service\Ns\EstabelecimentosService;
use Nasajon\MDABundle\Service\Meurh\solicitacoeshistoricosService;
use Nasajon\MDABundle\Service\Persona\TiposdocumentoscolaboradoresService;

class SolicitacoesdocumentosServiceTest extends \Codeception\Test\Unit
{

    protected $logged_user = ["nome" => "Teste", "email" => "teste@nasajon.com.br", "id" => "31c06307-4b04-45da-886a-608f00172a15"];
    protected $solicitacoesdocumentosRepository;
    protected $solicitacoesService;
    protected $estabelecimentosService;
    protected $adapter;
    protected $fixedAttributes;
    protected $solicitacoesdocumentosService;
    protected $solicitacoeshistoricosService;
    protected $tiposdocumentoscolaboradoresService;

    public function setUp() {
        $this->solicitacoesdocumentosRepository = $this->getMockBuilder(SolicitacoesdocumentosRepository::class)
            ->setMethods(['begin', 'commit', 'rollBack', 'getRepository', 'update', 'insert', 'delete', 'prepare', 'execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->solicitacoesService = $this->getMockBuilder(SolicitacoesService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->estabelecimentosService = $this->getMockBuilder(EstabelecimentosService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->solicitacoeshistoricosService = $this->getMockBuilder(SolicitacoeshistoricosService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tiposdocumentoscolaboradoresService = $this->getMockBuilder(TiposdocumentoscolaboradoresService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fixedAttributes = \Codeception\Util\Stub::makeEmpty(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag::class);

        $this->adapter = \Codeception\Util\Stub::makeEmpty(\Gaufrette\Adapter::class);

        $this->solicitacoesdocumentosService = $this->getMockBuilder(SolicitacoesdocumentosService::class)
        ->disableOriginalConstructor()
        ->getMock();
    }

    public function testInsereSolicitacaoDocumentoComSucessoQuandoSolicitacaoTemSicutacaoIgualAZero() {
        $solicitacao = [
            "solicitacao" => '3bda2ecb-2170-4b75-b072-ff3c51de4503',
            "situacao" => 0,
            "tenant" => 47
        ];

        $entity = new Solicitacoesdocumentos();

        $entity->setSolicitacao("3bda2ecb-2170-4b75-b072-ff3c51de4503");
        $entity->setConteudo("file");
        $entity->setTenant(47);

        $this->solicitacoesService
            ->expects($this->any())
            ->method('find')
            ->with($solicitacao["solicitacao"], $solicitacao["tenant"])
            ->willReturn($solicitacao);

        $this->solicitacoesdocumentosService
            ->expects($this->any())
            ->method('createFile')
            ->with("enderecodoarquivo", $entity->getConteudo())
            ->willReturn(true);

        $this->solicitacoesdocumentosRepository
            ->expects($this->any())
            ->method('insert')
            ->with($this->logged_user, $entity->getTenant(), $entity)
            ->willReturn($entity->getSolicitacaodocumento());

        $response = $this->solicitacoesdocumentosService->insert($this->logged_user, $entity->getTenant(), $entity);
        $this->assertEquals($entity->getSolicitacaodocumento(), $response);
    }


    public function testInsereSolicitacaoDocumentoComFalhaQuandoSolicitacaoTemSituacaoDiferenteDeZero() {
        $solicitacao = [
            "solicitacao" => '3bda2ecb-2170-4b75-b072-ff3c51de4503',
            "situacao" => 1,
            "tenant" => 47
        ];

        $entity = new Solicitacoesdocumentos();

        $entity->setSolicitacao("3bda2ecb-2170-4b75-b072-ff3c51de4503");
        $entity->setConteudo("file");
        $entity->setTenant(47);

        $this->solicitacoesService
            ->expects($this->any())
            ->method('find')
            ->with($solicitacao["solicitacao"], $solicitacao["tenant"])
            ->willReturn($solicitacao);

        $this->estabelecimentosService
            ->expects($this->any())
            ->method('findEstabelecimentoComGrupo')
            ->with($solicitacao["tenant"], "3bda2ecb-2170-4b75-b072-ff3c51de4503")
            ->willReturn(array("grupoempresarial" => "gednasajon"));      

        $this->solicitacoeshistoricosService
            ->expects($this->any())
            ->method('find')
            ->with("3bda2ecb-2170-4b75-b072-ff3c51de4503", $solicitacao["tenant"])
            ->willReturn(["anexos" => []]);
        
        $this->tiposdocumentoscolaboradoresService
            ->expects($this->any())
            ->method('find')
            ->with("3bda2ecb-2170-4b75-b072-ff3c51de4503", $solicitacao["tenant"])
            ->willReturn(["descricao" => "Descricao Teste"]);

        $solicitacoesdocumentosService = new SolicitacoesdocumentosService($this->solicitacoesdocumentosRepository, $this->adapter, $this->solicitacoesService, $this->estabelecimentosService,  $this->fixedAttributes, $this->solicitacoeshistoricosService, $this->tiposdocumentoscolaboradoresService);
        $this->expectException("Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException");
        $solicitacoesdocumentosService->insert(["nome"=> "teste"], $entity->getTenant(), $entity);
    }

    public function testDeletaSolicitacaoDocumentoComSucessoQuandoSolicitacaoTemSicutacaoIgualAZero() {
        $solicitacao = [
            "solicitacao" => '3bda2ecb-2170-4b75-b072-ff3c51de4503',
            "situacao" => 0,
            "tenant" => 47
        ];

        $entity = new Solicitacoesdocumentos();

        $entity->setSolicitacao("3bda2ecb-2170-4b75-b072-ff3c51de4503");
        $entity->setConteudo("file");
        $entity->setTenant(47);

        $this->solicitacoesService
            ->expects($this->any())
            ->method('find')
            ->with($solicitacao["solicitacao"], $solicitacao["tenant"])
            ->willReturn($solicitacao);

        $this->solicitacoesdocumentosService
            ->expects($this->any())
            ->method('deleteFile')
            ->with("enderecodoarquivo")
            ->willReturn(true);

        $this->solicitacoesdocumentosRepository
            ->expects($this->any())
            ->method('delete')
            ->with($entity->getTenant(), $entity)
            ->willReturn($entity->getSolicitacaodocumento());

        $response = $this->solicitacoesdocumentosService->delete($entity->getTenant(), $entity);
        $this->assertEquals($entity->getSolicitacaodocumento(), $response);
    }


    public function testDeletaSolicitacaoDocumentoComFalhaQuandoSolicitacaoTemSituacaoDiferenteDeZero() {
        $solicitacao = [
            "solicitacao" => '3bda2ecb-2170-4b75-b072-ff3c51de4503',
            "situacao" => 1,
            "tenant" => 47
        ];

        $entity = new Solicitacoesdocumentos();

        $entity->setSolicitacao("3bda2ecb-2170-4b75-b072-ff3c51de4503");
        $entity->setConteudo("file");
        $entity->setTenant(47);

        $this->solicitacoesService
            ->expects($this->any())
            ->method('find')
            ->with($solicitacao["solicitacao"], $solicitacao["tenant"])
            ->willReturn($solicitacao);

        $this->solicitacoeshistoricosService
            ->expects($this->any())
            ->method('find')
            ->with("3bda2ecb-2170-4b75-b072-ff3c51de4503", $solicitacao["tenant"])
            ->willReturn(array());
        
        $this->tiposdocumentoscolaboradoresService
            ->expects($this->any())
            ->method('find')
            ->with("3bda2ecb-2170-4b75-b072-ff3c51de4503", $solicitacao["tenant"])
            ->willReturn(array());

        $solicitacoesdocumentosService = new SolicitacoesdocumentosService($this->solicitacoesdocumentosRepository, $this->adapter, $this->solicitacoesService, $this->estabelecimentosService, $this->fixedAttributes, $this->solicitacoeshistoricosService, $this->tiposdocumentoscolaboradoresService);
        $this->expectException("Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException");
        $solicitacoesdocumentosService->delete($entity->getTenant(), $entity);
    }
}