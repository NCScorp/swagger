<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Service\MailReceiverService;

class MailReceiverServiceTest extends \Codeception\Test\Unit {
    
    private $service;
    private $atendimentoRepo;
    private $followupRepo;
    private $followupClientesRepo;
    private $documentosgedRepository;
    private $usuarioRepo;
    private $clienteRepo;
    private $emailOriginalRepo;
    private $enderecosemailsRepo;
    private $tenantRepo;
    private $em;
    private $configuracoesService;
    private $purifier;
    private $filesystemMap;
    private $adapterImg;
    
    protected function setUp() {
        $this->atendimentoRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Cliente\SolicitacoesRepository')->disableOriginalConstructor()->getMock();
        $this->followupClientesRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Cliente\FollowupsRepository')->disableOriginalConstructor()->getMock();
        $this->followupRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\FollowupsRepository')->disableOriginalConstructor()->getMock();
        $this->documentosgedRepository = $this->getMockBuilder('Nasajon\MDABundle\Repository\Ns\DocumentosgedRepository')->disableOriginalConstructor()->getMock();
        $this->usuarioRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Cliente\UsuariosRepository')->disableOriginalConstructor()->getMock();
        $this->clienteRepo = $this->getMockBuilder('Nasajon\MDABundle\Repository\Ns\ClientesRepository')->disableOriginalConstructor()->getMock();
        $this->emailOriginalRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Servicos\AtendimentosemailsoriginaisRepository')->disableOriginalConstructor()->getMock();
        $this->tenantRepo = $this->getMockBuilder('Nasajon\ModelBundle\Repository\Ns\TenantsRepository')->disableOriginalConstructor()->getMock();
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();        
        $this->enderecosemailsRepo = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\EnderecosemailsRepository')->disableOriginalConstructor()->getMock();
        $this->purifier = $this->getMockBuilder('\HTMLPurifier')->disableOriginalConstructor()->getMock(); 
        $this->filesystemMap =  $this->getMockBuilder('Knp\Bundle\GaufretteBundle\FilesystemMap')->disableOriginalConstructor()->getMock(); 
        $this->adapterImg = null;
        $mailgunKey = null;
        $anexosFileSystem = null;
        $this->configuracoesService = $this->getMockBuilder('Nasajon\ModelBundle\Services\ConfiguracoesService')->disableOriginalConstructor()->getMock();        
        $this->service = new MailReceiverService($this->atendimentoRepo, $this->followupClientesRepo, $this->followupRepo, $this->documentosgedRepository, $this->usuarioRepo, $this->clienteRepo, 
                $this->emailOriginalRepo, $this->em, $mailgunKey, $this->enderecosemailsRepo, $anexosFileSystem, $this->configuracoesService, $this->purifier, $this->filesystemMap,  $this->adapterImg);
    }
    
    
    public function obterEmails() {
        return array(
            array("Foo <foo@gmail.com>, Bar <bar@gmail.com>", [0 => "foo@gmail.com", 1 => "bar@gmail.com"])
        );
    }
    
    /**
     * @dataProvider obterEmails
     */
    public function testObterEmails($emails, $match) {
        $arrReceptores = $this->service->obterEmails($emails);        
        $this->assertEquals(2, count($arrReceptores));
        $this->assertEquals($match, $arrReceptores);
    }
    
    public function geraListaReceptores() {
        return array(
            array(["To" => "Foo <foo@gmail.com>", "Cc" => "Bar <bar@gmail.com>", "Bcc" => "Baz <baz@gmail.com>"], [ 0 => "foo@gmail.com", 1 => "bar@gmail.com", 2 => "baz@gmail.com" ]),
            array([ "To" => "Foo <foo@gmail.com>, Qux <qux@gmail.com>", "Cc" => "Bar <bar@gmail.com>", "Bcc" => "Baz <baz@gmail.com>" ], [ 0 => "foo@gmail.com", 1 => "qux@gmail.com", 2 => "bar@gmail.com", 3 => "baz@gmail.com" ]),
            array([ "To" => "Foo <foo@gmail.com>", "Bcc" => "Baz <baz@gmail.com>" ], [ 0 => "foo@gmail.com", 1 => "baz@gmail.com" ]),
            array([ "To" => "Foo <foo@gmail.com>", "Cc" => "Bar <bar@gmail.com>" ], [ 0 => "foo@gmail.com", 1 => "bar@gmail.com" ]),
            array([ "Cc" => "Bar <bar@gmail.com>, Qux <qux@gmail.com>" ], [ 0 => "bar@gmail.com", 1 => "qux@gmail.com" ])
        );
    }
    
    /**
     * @dataProvider geraListaReceptores
     */
    public function testGerarListaReceptores($emails, $match) {
        $arrReceptores = $this->service->geraListaReceptores($emails);
        $this->assertEquals($match, $arrReceptores);
    }
    
    public function geraSpamScore() {
        return array(
            array([ 'X-Mailgun-Sflag' => 'Yes', 'X-Mailgun-Spf' => 'Pass' ], false, FALSE),
            array([ 'X-Mailgun-Sscore' => 5, 'X-Mailgun-Sflag' => 'Yes', 'X-Mailgun-Spf' => 'Pass' ], false, TRUE),
            array([ 'X-Mailgun-Sscore' => 100, 'X-Mailgun-Sflag' => 'Yes', 'X-Mailgun-Spf' => 'Neutral' ], true, FALSE)
        );
    }
    
    /**
     * @dataProvider geraSpamScore
     */
    public function testSpamScore($message, $isUser, $match) {
        $spam = $this->service->geraSpamScore($message, $isUser);        
        $this->assertEquals($match, $spam);
    }
    
    public function defineCanalEmailRecipient() {
        $tenant = $this->getMockBuilder('Nasajon\ModelBundle\Entity\Ns\Tenants')->disableOriginalConstructor()->getMock();
        $tenant->method('getId')->willReturn(47);
        
        return array(
            array(['recipient' => 'foo@gmail.com'], [0 => 'bar@gmail.com'], $tenant, 'foo@gmail.com'),
            array(['recipient' => 'foo@gmail.com'], [0 => 'lol@gmail.com'], $tenant, 'foo@gmail.com')
        );
    }
    
    /**
     * @dataProvider defineCanalEmailRecipient
     */
    public function testDefineCanalEmailRecipient($message, $receptores, $tenant, $match) {
        $canal_email = $this->service->defineCanalEmail($message, $receptores, $tenant, $match);        
        $this->assertEquals($match, $canal_email);
    }
    
    
    public function defineCanalEmail() {
        $tenant = $this->getMockBuilder('Nasajon\ModelBundle\Entity\Ns\Tenants')->disableOriginalConstructor()->getMock();
        $tenant->method('getId')->willReturn(47);
        
        return array(
            array(['recipient' => 'foo@gmail.com'], [0 => 'bar@gmail.com'], $tenant, 'bar@gmail.com'),
            array(['recipient' => 'foo@gmail.com'], [0 => 'lol@gmail.com'], $tenant, 'lol@gmail.com')
        );
    }
    
    /**
     * @dataProvider defineCanalEmail
     */
    public function testDefineCanalEmail($message, $receptores, $tenant, $match) {
        $this->enderecosemailsRepo->method('findByEmail')->willReturn('guid');
        $canal_email = $this->service->defineCanalEmail($message, $receptores, $tenant, $match);        
        $this->assertEquals($match, $canal_email);
    }
}
