<?php

namespace Nasajon\AppBundle\Service\Crm;

use Nasajon\MDABundle\Entity\Crm\Atcs;
use Nasajon\AppBundle\DTO\Ns\ClientesDTO;
use Nasajon\AppBundle\DTO\Crm\AtcsRelatorioDTO;
use Nasajon\AppBundle\DTO\Financas\ItemRpsDTO;
use Nasajon\AppBundle\DTO\Financas\RpsDTO;
use Nasajon\AppBundle\DTO\Ns\EstabelecimentoDTO;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AtcsRelatoriosRpsService
{
    protected $repository;
    protected $tokenStorage;
    protected $fixedAttributes;

    public function __construct(
        \Nasajon\AppBundle\Repository\Crm\AtcsRelatoriosRpsRepository $repository,
        TokenStorageInterface $tokenStorage,
        ParameterBag $fixedAttributes
    ) {
        $this->repository = $repository;
        $this->tokenStorage = $tokenStorage;
        $this->fixedAttributes = $fixedAttributes;
    }

    public function montaEntidade(\Nasajon\MDABundle\Entity\Crm\Atcs $atcsObject, $contrato, $tenant, $id_grupoempresarial)
    {
        if($contrato == null || $contrato == '') {
            throw new \LogicException("Não é possível gerar RPS sem enviar o contrato.", 1);
        }
        $tenantCodigo = $this->fixedAttributes->get('tenant_codigo');

        $contratoTaxa = $atcsObject->getContratotaxaadm();

        $contratoTaxaAdm = false;
        if(is_object($contratoTaxa)) {
            $contratoTaxaAdm = $contratoTaxa->getContrato() == $contrato ? true : false;
        }

        $dadosBaseContrato = $this->repository->getDadosBaseContratos($atcsObject->getNegocio(), $contrato, $tenant, $id_grupoempresarial, $contratoTaxaAdm);
        $estabelecimento = $this->repository->getEstabelecimento($atcsObject->getEstabelecimento()->getEstabelecimento(), $tenant, $id_grupoempresarial);
        $itensNota = $this->repository->getItensNota($contrato, $tenant, $id_grupoempresarial, $contratoTaxaAdm);

        if($contratoTaxaAdm){
            $responsavelFinanceiro = $atcsObject->getCliente()->getCliente();
        } else {
            $responsavelFinanceiro = $itensNota[0]['respfin_responsavelfinanceiro'];
        }

        $cliente = $this->repository->getCliente($responsavelFinanceiro, $tenant);
        $contatoCliente = $this->repository->getContatoPrincipalCliente($responsavelFinanceiro, $tenant);

        // Organizo dados do Cliente/Seguradora do Atendimento
        $cliente = [
            'contatoPrincipal' => $contatoCliente,
            'codigo' => $cliente['cliente_codigo'],
            'razaosocial' => $cliente['cliente_nome'],
            'nomefantasia' => $cliente['cliente_nomefantasia'],
            'cnpjcpf' => $cliente['cliente_cnpj'],
            'inscricaomunicipal' => $cliente['cliente_inscricaomunicipal'],
            'inscricaoestadual' => $cliente['cliente_inscricaoestadual'],
            'anotacoes' => $cliente['cliente_anotacao'],
            'enderecolocal' => [
                'tipologradouro' => $cliente['cliente_endlocal_tipologradouro'],
                'logradouro' => $cliente['cliente_endlocal_logradouro'],
                'numero' => $cliente['cliente_endlocal_numero'],
                'complemento' => $cliente['cliente_endlocal_complemento'],
                'cep' => $cliente['cliente_endlocal_cep'],
                'bairro' => $cliente['cliente_endlocal_bairro'],
                'uf' => $cliente['cliente_endlocal_uf'],
                'paisnome' => $cliente['cliente_endlocal_pais'],
                'municipionome' => $cliente['cliente_endlocal_municipio'],
                'referencia' => $cliente['cliente_endlocal_referencia'],
                'nome' => $cliente['cliente_endlocal_nome']
            ],
            'enderecocobranca' => [
                'tipologradouro' => $cliente['cliente_endcob_tipologradouro'],
                'logradouro' => $cliente['cliente_endcob_logradouro'],
                'numero' => $cliente['cliente_endcob_numero'],
                'complemento' => $cliente['cliente_endcob_complemento'],
                'cep' => $cliente['cliente_endcob_cep'],
                'bairro' => $cliente['cliente_endcob_bairro'],
                'uf' => $cliente['cliente_endcob_uf'],
                'paisnome' => $cliente['cliente_endcob_pais'],
                'municipionome' => $cliente['cliente_endcob_municipio'],
                'referencia' => $cliente['cliente_endcob_referencia'],
                'nome' => $cliente['cliente_endcob_nome']
            ]
        ];

        $cliente['enderecolocal']['enderecocompleto'] = $cliente['enderecolocal']['tipologradouro'] . ' ' .
            $cliente['enderecolocal']['logradouro'] . ', ' . $cliente['enderecolocal']['numero'] . ' - ' .
            $cliente['enderecolocal']['bairro'] . ' - ' . $cliente['enderecolocal']['municipionome'] . ' - ' .
            $cliente['enderecolocal']['uf'] . ' - CEP: ' . $cliente['enderecolocal']['cep'];
        
        $cliente['enderecocobranca']['enderecocompleto'] = $cliente['enderecocobranca']['tipologradouro'] . ' ' .
            $cliente['enderecocobranca']['logradouro'] . ', ' . $cliente['enderecocobranca']['numero'] . ' - ' .
            $cliente['enderecocobranca']['bairro'] . ' - ' . $cliente['enderecocobranca']['municipionome'] . ' - ' .
            $cliente['enderecocobranca']['uf'] . ' - CEP: ' . $cliente['enderecocobranca']['cep'];

        
        $estabelecimento = [
            'razaosocial' => $estabelecimento['razaosocial'],
            'email' => $estabelecimento['email'],
            'nomefantasia' => $estabelecimento['nomefantasia'],
            'cnpjcpf' => $estabelecimento['cnpj_completo'],
            'inscricaomunicipal' => $estabelecimento['inscricaomunicipal'],
            'inscricaoestadual' => $estabelecimento['inscricaoestadual'],
            'telefone' => $estabelecimento['telefonecomddd'],
            'pathlogo' => $this->tokenStorage->getToken()->getUser()->getTenants()[$tenantCodigo]->getLogo(),
            'endereco' => [
                'tipologradouro' => $estabelecimento['end_tipologradouro'],
                'logradouro' => $estabelecimento['end_logradouro'],
                'numero' => $estabelecimento['end_numero'],
                'complemento' => $estabelecimento['end_complemento'],
                'cep' => $estabelecimento['end_cep'],
                'bairro' => $estabelecimento['end_bairro'],
                'uf' => $estabelecimento['end_uf'],
                'paisnome' => $estabelecimento['end_paisnome'],
                'municipionome' => $estabelecimento['end_municipionome']
            ]
        ];

        $estabelecimento['endereco']['enderecocompleto'] = $estabelecimento['endereco']['tipologradouro'] . ' ' .
            $estabelecimento['endereco']['logradouro'] . ', ' . $estabelecimento['endereco']['numero'] . ' - ' .
            $estabelecimento['endereco']['bairro'] . ' - ' . $estabelecimento['endereco']['municipionome'] . ' - ' .
            $estabelecimento['endereco']['uf'] . ' - CEP: ' . $estabelecimento['endereco']['cep'];

        $atcsRelatorioDTO = new AtcsRelatorioDTO();
        $atcsObject = new Atcs();

        $rpsDTO = new RpsDTO();
        $rpsDTO->fillDTO($dadosBaseContrato);
        $rpsDTO->setCfop($itensNota[0]);
        foreach ($itensNota as $key => $itemNota) {
            $ItemRpsDTO = new ItemRpsDTO();
            $ItemRpsDTO->fillDTO($itemNota);
            $rpsDTO->addItemNota($ItemRpsDTO);
        }
        $atcsRelatorioDTO->setRps($rpsDTO);

        $clienteDTO = new ClientesDTO();
        $clienteDTO->fillDTO($cliente);
        $atcsObject->setCliente($clienteDTO);

        $estabelecimentoDto = new EstabelecimentoDTO();
        $estabelecimentoDto->fillDTO($estabelecimento);
        $atcsObject->setEstabelecimento($estabelecimentoDto);

        $atcsRelatorioDTO->setAtcs($atcsObject);

        return $atcsRelatorioDTO;
    }
}
