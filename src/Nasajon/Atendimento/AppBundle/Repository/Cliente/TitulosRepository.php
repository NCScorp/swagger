<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Cliente;

use Doctrine\ORM\EntityManager;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Repository\Atendimento\Cliente\TitulosRepository as ParentRepository;
use Nasajon\MDABundle\Repository\Financas\ConfiguracoesbancariasRepository;
use Nasajon\MDABundle\Repository\Ns\EstabelecimentoRepository;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\ModelBundle\Services\ConfiguracoesService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGenerator;

class TitulosRepository extends ParentRepository {

    /**
     *
     * @var Router
     */
    private $router;

    /**
     *
     * @var EstabelecimentoRepository
     */
    private $estabelecimentoRepository;

    /**
     *
     * @var ConfiguracoesbancariasRepository
     */
    private $fnncsCnfgrcsbncrsRpstry;

    /**
     *
     * @var ContasRepository
     */
    private $contasRepository;

    /**
     *
     * @var \Nasajon\MDABundle\Repository\Ns\EnderecosRepository
     */
    private $enderecoRepository;

    /**
     *
     * @var confService
     */
    private $confService;

    private $sentryEntry;

    public function __construct($connection, $fnncsCnfgrcsbncrsRpstry, $estabelecimentoRepository, $contasRepository, $enderecoRepository, Router $router, EntityManager $em, $confService, $sentryEntry) {
        parent::__construct($connection);

        $this->fnncsCnfgrcsbncrsRpstry = $fnncsCnfgrcsbncrsRpstry;
        $this->estabelecimentoRepository = $estabelecimentoRepository;
        $this->contasRepository = $contasRepository;
        $this->enderecoRepository = $enderecoRepository;
        $this->confService = $confService;
        $this->router = $router;
        $this->em = $em;

        $this->sentryEntry = $sentryEntry;
    }

    /**
     * {@inheritDoc}
     */
    public function findAll($tenant, $cliente, Filter $filter = null) {

        $result = parent::findAll($tenant, $cliente, $filter);
        $tenantObj = $this->em->getRepository("Nasajon\ModelBundle\Entity\Ns\Tenants")->find($tenant);

        for ($i = 0; $i < count($result); $i++) {
            $links = [];
            if (in_array($result[$i]['situacao'], [0, 2])) {
                $links['boleto'] = $this->generateAtendimentoUrl('atendimento_cliente_titulos_get', ["tenant" => $tenantObj->getCodigo(), "id_pessoa" => $cliente, "id" => $result[$i]['id']]);
            }
            if ($result[$i]['tipo'] == 0 && $result[$i]['modelo'] == 'NE') {
                $links['nfe_xml'] = $this->generateAtendimentoUrl('atendimento_cliente_titulos_nota', ["tenant" => $tenantObj->getCodigo(), "id_pessoa" => $cliente, "id" => $result[$i]['id'], "_format" => "xml"]);
                $links['nfe_pdf'] = $this->generateAtendimentoUrl('atendimento_cliente_titulos_nota', ["tenant" => $tenantObj->getCodigo(), "id_pessoa" => $cliente, "id" => $result[$i]['id'], "_format" => "pdf"]);
            } elseif ($result[$i]['tipo'] == 5) {
                $links['nfse_xml'] = $this->generateAtendimentoUrl('atendimento_cliente_titulos_nota', ["tenant" => $tenantObj->getCodigo(), "id_pessoa" => $cliente, "id" => $result[$i]['id'], "_format" => "xml"]);
                $links['nfse_pdf'] = $this->generateAtendimentoUrl('atendimento_cliente_titulos_nota', ["tenant" => $tenantObj->getCodigo(), "id_pessoa" => $cliente, "id" => $result[$i]['id'], "_format" => "pdf"]);
            }

            $result[$i]['boleto_anexo'] = $this->possuiBoleto($result[$i]['id'], $tenant);

            $result[$i]['links'] = $links;
        }
        return $result;
    }

    /**
     * Esse método foi desenvolvido devido a um problema proveniente da duplicação da aplicação do Atendimento em Produção.
     * 
     * Uma das aplicações apontava para atendimento.nasajon.com.br, a outra, para hubspot.nasajon.com.br, e quado o route gerava a url, ela era gerada com 127.0.0.1:9000.
     * 
     * Para mitigar o problema, quando a aplicação for em produção, sempre direciona para atendimento.nasajon.com.br.
     * 
     * @return String
     */
    private function generateAtendimentoUrl($nameRoute, $data): String
    {
        if (strtolower($this->sentryEntry) === 'prod' || strtolower($this->sentryEntry) === 'production') {
            return 'https://atendimento.nasajon.com.br' . $this->router->generate($nameRoute, $data);
        }

        return $this->router->generate($nameRoute, $data, UrlGenerator::ABSOLUTE_URL);
    }

    public function possuiBoleto($id, $tenant) {

        $sql = "select d.documentoged
                from ns.documentosged d 
                join ns.anexosmodulos a on a.documentoged = d.documentoged 
                join financas.titulos t on t.id_conjunto_anexo = a.id_modulodoanexo
                join ns.pessoas p on p.id = t.id_pessoa
                where t.id = ? and t.tenant = ? 
                    and replace(a.descricao, 'boleto_','') = p.pessoa || '_' || t.vencimentotexto
                ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$id, $tenant]);

        $result = $stmt->fetch();

        return $result ? $result['documentoged'] : null;
    }

    public function buscarBoleto($id, $tenant) {

        $sql = "select documento
                from ns.documentosged d 
                join ns.anexosmodulos a on a.documentoged = d.documentoged 
                join financas.titulos t on t.id_conjunto_anexo = a.id_modulodoanexo
                join ns.pessoas p on p.id = t.id_pessoa
                where t.id = ? and t.tenant = ? and a.tipo = 5
                    and replace(a.descricao, 'boleto_','') = p.pessoa || '_' || t.vencimentotexto
                "; // Filtra pelo tipo do anexo - 5 = PDF

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$id, $tenant]);

        $result = $stmt->fetch();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function find($id, $tenant, $cliente) {

        $entity = parent::find($id, $tenant, $cliente);

        $entity['estabelecimento'] = $this->estabelecimentoRepository->find($entity['estabelecimento']['id']);

        $configuracoes = $this->fnncsCnfgrcsbncrsRpstry->findAll($entity['layoutcobranca'], $tenant);
        $entity['configuracoesbancarias'] = [];
        foreach ($configuracoes as $configuracao) {
            $entity['configuracoesbancarias'][$configuracao['nome']] = $configuracao['valor'];
        }

        $endereco = $this->enderecoRepository->findAll($tenant, $cliente, 2);

        $entity['enderecocobranca'] = isset($endereco[0]) ? $endereco[0] : NULL;

        $entity['conta'] = $this->contasRepository->find($entity['conta']['conta']);

        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function fillEntity($data) {

        $entity = parent::fillEntity($data);

        $entity->setEnderecocobranca(EntityHydrator::hydrate('Nasajon\MDABundle\Entity\Ns\Enderecos', $entity->getEnderecocobranca()));

        return $entity;
    }

    public function findAllQueryBuilderBody(&$queryBuilder, $tenant, $cliente, Filter $filter = null) {

        $binds = parent::findAllQueryBuilderBody($queryBuilder, $tenant, $cliente, $filter);

        $situacoesExibir = $this->getSituacoesExibir($tenant);
        if (count($situacoesExibir) > 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('t0_.situacao', array_map(function($a) {
                                return $a;
                            }, $situacoesExibir))
            );
        }
         $mesesAtras = $this->confService->get($tenant, 'ATENDIMENTO', 'TITULO_PERIODO_DE_EXIBICAO_PASSADO');
         if($mesesAtras != 0){
             $queryBuilder->andWhere($queryBuilder->expr()->gte("t0_.vencimento", "current_date - INTERVAL '".$mesesAtras." months'"));
         }

        return $binds;
    }

    private function getSituacoesExibir($tenant) {
        return json_decode($this->confService->get($tenant, 'ATENDIMENTO', 'TITULOS_EXIBIR_SEGUINTES_SITUACOES'));
    }

}
