<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Service\RegrasAtendimentoService;
use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosregrasRepository;
use Nasajon\Atendimento\AppBundle\Repository\Admin\EnderecosemailsRepository;
use Nasajon\MDABundle\Repository\Ns\ClientesRepository;
use Nasajon\MDABundle\Entity\Ns\Clientes;
use Nasajon\MDABundle\Entity\Atendimento\Admin\Solicitacoes;
use \Codeception\Stub;
use Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosfilasRepository;

class RegrasAtendimentoServiceTest extends \Codeception\Test\Unit {

    private $service;

    protected function setUp() {
        $atendimentosregrasRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosregrasRepository')->disableOriginalConstructor()->getMock();
        $enderecoemailRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\EnderecosemailsRepository')->disableOriginalConstructor()->getMock();
        $clientesRepository = $this->getMockBuilder('Nasajon\MDABundle\Repository\Ns\ClientesRepository')->disableOriginalConstructor()->getMock();
        $filasRepository = $this->getMockBuilder('Nasajon\Atendimento\AppBundle\Repository\Admin\AtendimentosfilasRepository')->disableOriginalConstructor()->getMock();

        $this->service = new RegrasAtendimentoService($atendimentosregrasRepository, $enderecoemailRepository, $clientesRepository, $filasRepository);
    }

    /**
     * @dataProvider getDebugAndCount
     */
    public function testUrls($debug, $count, $message) {

    }

    public function getDebugAndCount() {
        return array(
            array(false, 1, '->javascripts() returns one url when not in debug mode'),
            array(true, 2, '->javascripts() returns many urls when in debug mode'),
        );
    }

    private function getAtendimentoRegra($tipoRegra) {
        switch($tipoRegra){
            case 1:
                return array (
                    'atendimentoregra' => 'f26753d9-0150-418c-9b9b-c8e9ea8b4835',
                    'nome' => 'Teste',
                    'ordem' => 0,
                    'naoexecutarregrasubsequente' => false,
                    'tenant' => 47,
                    'updated_at' => '2019-07-09 17:47:15+00',
                    'updated_by' => 
                    array (
                    'nome' => 'Wilson Santos',
                    'email' => 'wilsonsantos@nasajon.com.br',
                    ),
                    'created_at' => '2019-07-02 14:58:08+00',
                    'created_by' => 
                    array (
                    'nome' => 'Wilson Santos',
                    'email' => 'wilsonsantos@nasajon.com.br',
                    ),
                    'acoes' => 
                    array (
                        array (
                        'atendimentoregraacao' => '6e268b48-8be0-487c-981a-93da54cab4b1',
                        'acao' => 'fechar_atendimento',
                        'acaocampocustomizado' => NULL,
                        'valor' => NULL,
                        ),
                    ),
                    'condicoes_in' => array (),
                    'condicoes_ex' => 
                    array (
                    array (
                        'atendimentoregracondicao' => 'd70d42e9-5e58-479c-9291-736aa0002bb3',
                        'campo' => 'email',
                        'tipo' => 1,
                        'operador' => 'includes',
                        'valor' => 'cliente',
                        'tipoentidade' => NULL,
                    ),
                    array (
                        'atendimentoregracondicao' => '68070667-d280-4fe1-b142-a1795cd5da4e',
                        'campo' => 'sintoma',
                        'tipo' => 1,
                        'operador' => 'includes',
                        'valor' => 'erro',
                        'tipoentidade' => NULL,
                    ),
                    ),
                );
            break;
            case 2:
            return array (
                'atendimentoregra' => 'f26753d9-0150-418c-9b9b-c8e9ea8b4835',
                'nome' => 'Teste',
                'ordem' => 0,
                'naoexecutarregrasubsequente' => false,
                'tenant' => 47,
                'updated_at' => '2019-07-09 17:47:15+00',
                'updated_by' => 
                array (
                'nome' => 'Wilson Santos',
                'email' => 'wilsonsantos@nasajon.com.br',
                ),
                'created_at' => '2019-07-02 14:58:08+00',
                'created_by' => 
                array (
                'nome' => 'Wilson Santos',
                'email' => 'wilsonsantos@nasajon.com.br',
                ),
                'acoes' => 
                array (
                    array (
                    'atendimentoregraacao' => '6e268b48-8be0-487c-981a-93da54cab4b1',
                    'acao' => 'fechar_atendimento',
                    'acaocampocustomizado' => NULL,
                    'valor' => NULL,
                    ),
                ),
                'condicoes_in' => array (),
                'condicoes_ex' => 
                array (
                    array (
                        'atendimentoregracondicao' => 'aaecdadc-5347-46e2-ab56-83377c04ee01',
                        'campo' => 'canalemail',
                        'tipo' => 1,
                        'operador' => 'is_equal',
                        'valor' => '5bbbb65b-5871-4232-9d29-91ffc7deebfa',
                        'tipoentidade' => NULL,
                    ),
                    array (
                        'atendimentoregracondicao' => '2f1b2f9b-2950-4b6f-b035-bcf1318b7560',
                        'campo' => 'status_suporte',
                        'tipo' => 1,
                        'operador' => 'is_equal',
                        'valor' => 'Ativo',
                        'tipoentidade' => NULL,
                    )
                ),
            );
            break;
        }
    }

    private function getEmails(){
        return array (
            'enderecoemail' => '5bbbb65b-5871-4232-9d29-91ffc7deebfa',
            'email' => 'tenant@nasajon.com.br',
            'ativo' => false,
            'created_at' => '2019-07-09 19:06:40+00',
            'updated_at' => NULL,
            'created_by' => 
            array (
              'nome' => 'Wilson Santos',
              'email' => 'wilsonsantos@nasajon.com.br',
            ),
            'updated_by' => NULL,
            'tenant' => 47,
        );
    }

    private function getCliente() {
        return array (
            'cliente' => 'd323937a-a7d7-47bf-bae0-5a5372cc488e',
            'codigo' => '1',
            'nome' => 'Cliente Desenvolvimento',
            'nomefantasia' => 'Cliente Desenvolvimento',
            'cpf' => NULL,
            'cnpj' => '64976329000175',
            'datacliente' => NULL,
            'bloqueado' => false,
            'justificativasituacaopagamento' => NULL,
            'justificativatipoclientepagamento' => NULL,
            'tenant' => 47,
            'observacao' => NULL,
            'anotacao' => NULL,
            'email' => NULL,
            'emailcobranca' => NULL,
            'status_suporte' => 'Ativo',
            'datasituacaopagamento' => NULL,
            'datatipoclientepagamento' => NULL,
            'situacaopagamento' => 'Adimplente',
            'tipoclientepagamento' => 'Ativo',
            'restricaocobranca1' => NULL,
            'restricaocobranca2' => NULL,
            'representante_tecnico' => NULL,
            'representante' => NULL,
            'vendedor' => NULL,
            'contatos' => array (),
            'classificadores' => array (),
            'telefones' => array (),
            'ativos' => array (),
            'proximoscontatos' => array (),
            'usuarios' => array (),
            'flags' => array (),
            'enderecos' => array (),
            'solicitacoes' => array (),
            'pessoamunicipio' => array (),
        );
    }

    private function getAtendimento(){

        $cliente = new Clientes();
        $cliente->setCliente('d323937a-a7d7-47bf-bae0-5a5372cc488e');
        $cliente->setCnpj('64976329000175');
        $cliente->setCodigo('1');
        $cliente->setNome('Cliente Desenvolvimento');
        $cliente->setNomefantasia('Cliente Desenvolvimento');
        $cliente->setSituacaopagamento('Adimplente');
        $cliente->setStatusSuporte('Ativo');

        $atendimento = new Solicitacoes();
        $atendimento->setAtendimento('c25000bc-45e1-4f30-828d-ebda94334e02');
        $atendimento->setNumeroprotocolo(1);
        $atendimento->setSituacao(0);
        $atendimento->setEmail('cliente@nasajon.com.br');
        $atendimento->setResponsavelWeb('atendente@nasajon.com.br');
        $atendimento->setResponsavelWebTipo(0);
        $atendimento->setSintoma('<p>Ocorreu um erro no cadastro</p>');
        $atendimento->setResumo('Ocorreu um erro no cadastro');
        $atendimento->setCanal('email');
        $atendimento->setCanalEmail('tenant@nasajon.com.br');
        $atendimento->setCreatedBy(json_encode(['nome'=>'Cliente', 'email'=>'cliente@nasajon.com.br']));
        $atendimento->setCreatedAt('2019-06-27 15:00:54+00');
        $atendimento->setTenant(47);
        $atendimento->setVisivelparacliente(true);
        $atendimento->setAdiado(false);
        $atendimento->setCliente($cliente);

        return $atendimento;
    }

    private function getAtendimentoRegrasRepository($tipoRegra) {
        return Stub::make(AtendimentosregrasRepository::class,[
            'findAll' => function ($tenant) {
                if($tenant == 47){
                    return [
                        [
                            'atendimentoregra' => 'f26753d9-0150-418c-9b9b-c8e9ea8b4835',
                            'nome' => 'Teste',
                            'ordem' => 0,
                            'naoexecutarregrasubsequente' => false,
                        ]
                    ];
                }else{
                    return [];
                }
            },
            'find' => function($id, $tenant) use ($tipoRegra){
                if($tenant == 47 && $id == 'f26753d9-0150-418c-9b9b-c8e9ea8b4835' ){
                    return $this->getAtendimentoRegra($tipoRegra);
                }else{
                    return [];
                }
            }
        ]);   
    }

    private function getEnderecoEmailRepository() {
        return Stub::make(EnderecosemailsRepository::class, [
            'findByEmail' => function ($tenant, $canalemail){
                if($tenant == 47 && $canalemail == 'tenant@nasajon.com.br'){
                    return '5bbbb65b-5871-4232-9d29-91ffc7deebfa';
                }else{
                    return null;
                }
            }
        ]);
    }

    private function getClientesRepository() {
        return Stub::make(ClientesRepository::class, [
            'find' => function($id, $tenant){
                if($tenant == 47 && $id == 'd323937a-a7d7-47bf-bae0-5a5372cc488e'){
                    return $this->getCliente();
                }else {
                    return [];
                }
            }
        ]);
    }

    private function getAtendimentosFilasRepository() {
        return Stub::make(AtendimentosfilasRepository::class, [
            'findObject' => function($id, $tenant){
                if($tenant == 47){
                    return [
                        'nome' => 'Fila',
                        'atendimentofila' => 'e96bfdb8-d498-43f2-a5b2-857538c2de05',
                        'tenant' => 47
                    ];
                }else {
                    return [];
                }
            }
        ]);
    }

    // Teste E-mail e Sintoma 
    public function testEmailSintoma() {
        $atendimentosregrasRepository = $this->getAtendimentoRegrasRepository(1);
        $enderecoemailRepository = $this->getEnderecoEmailRepository();
        $clientesRepository = $this->getClientesRepository();
        $atendimento = $this->getAtendimento();
        $filaRepository = $this->getAtendimentosFilasRepository();
        $service = new RegrasAtendimentoService($atendimentosregrasRepository, $enderecoemailRepository, $clientesRepository, $filaRepository);
        $service->run(47, $atendimento);
        $this->assertEquals(1 , $atendimento->getSituacao() );
    }

    public function testSituacaoPagamentoCanalEmail() {
        $atendimentosregrasRepository = $this->getAtendimentoRegrasRepository(2);
        $enderecoemailRepository = $this->getEnderecoEmailRepository();
        $clientesRepository = $this->getClientesRepository();
        $filaRepository = $this->getAtendimentosFilasRepository();
        $atendimento = $this->getAtendimento();
        $service = new RegrasAtendimentoService($atendimentosregrasRepository, $enderecoemailRepository, $clientesRepository, $filaRepository);
        $service->run(47, $atendimento);
        $this->assertEquals(1 , $atendimento->getSituacao() );
    }
}
